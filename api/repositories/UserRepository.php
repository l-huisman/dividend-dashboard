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

    public function getAll(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $stmt = $this->connection->prepare(
            'SELECT id, username, email, role, created_at, updated_at FROM users ORDER BY id ASC LIMIT :limit OFFSET :offset'
        );
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

    public function count(): int
    {
        $stmt = $this->connection->query('SELECT COUNT(*) FROM users');

        return (int) $stmt->fetchColumn();
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
