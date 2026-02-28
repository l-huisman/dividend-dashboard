<?php

declare(strict_types=1);

namespace Repositories;

use Models\User;
use PDO;

class UserRepository extends Repository
{
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->rowToUser($row);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->rowToUser($row);
    }

    public function create(string $username, string $email, string $password): User
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->connection->prepare(
            'INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, 0)'
        );
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $hash,
        ]);

        $id = (int) $this->connection->lastInsertId();

        return $this->findById($id);
    }

    /**
     * @return User[]
     */
    public function getAll(int $page, int $limit, ?string $search = null, ?int $role = null): array
    {
        $offset = ($page - 1) * $limit;

        $sql = 'SELECT id, username, email, role, created_at, updated_at FROM users WHERE 1=1';
        $params = [];

        if ($search !== null) {
            $sql .= ' AND (username LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($role !== null) {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }

        $sql .= ' ORDER BY id ASC LIMIT :limit OFFSET :offset';

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User();
            $user->id = (int) $row['id'];
            $user->username = $row['username'];
            $user->email = $row['email'];
            $user->role = (int) $row['role'];
            $user->created_at = $row['created_at'];
            $user->updated_at = $row['updated_at'];
            $users[] = $user;
        }

        return $users;
    }

    public function count(?string $search = null, ?int $role = null): int
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE 1=1';
        $params = [];

        if ($search !== null) {
            $sql .= ' AND (username LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($role !== null) {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function countByRole(int $role): int
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);

        return (int) $stmt->fetchColumn();
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->connection->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function bulkUpdateRole(array $ids, int $role): int
    {
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->connection->prepare("UPDATE users SET role = ? WHERE id IN ({$placeholders})");
        $params = array_merge([$role], $ids);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        return $stmt->rowCount();
    }

    public function update(int $id, int $role): ?User
    {
        $stmt = $this->connection->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $role, 'id' => $id]);

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function rowToUser(array $row): User
    {
        $user = new User();
        $user->id = (int) $row['id'];
        $user->username = $row['username'];
        $user->email = $row['email'];
        $user->password_hash = $row['password_hash'] ?? '';
        $user->role = (int) $row['role'];
        $user->created_at = $row['created_at'];
        $user->updated_at = $row['updated_at'];

        return $user;
    }
}
