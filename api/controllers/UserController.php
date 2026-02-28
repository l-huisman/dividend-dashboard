<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Repositories\UserRepository;

class UserController extends Controller
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function getAll(): void
    {
        $token = AuthMiddleware::requireAdmin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));

        $search = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : null;
        $role = isset($_GET['role']) && $_GET['role'] !== '' ? (int) $_GET['role'] : null;

        $users = $this->repository->getAll($page, $limit, $search, $role);
        $total = $this->repository->count($search, $role);

        $this->respond([
            'data' => $users,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        ]);
    }

    public function update(string $id): void
    {
        $token = AuthMiddleware::requireAdmin();
        $adminId = (int) $token->data->id;
        $targetId = (int) $id;

        if ($adminId === $targetId) {
            $this->respondWithError(422, 'Cannot change own role');
            return;
        }

        $body = $this->getRequestBody();

        if ($body === null || !isset($body->role)) {
            $this->respondWithError(400, 'Role is required');
            return;
        }

        $role = (int) $body->role;

        if ($role !== 0 && $role !== 1) {
            $this->respondWithError(422, 'Role must be 0 or 1');
            return;
        }

        $user = $this->repository->update($targetId, $role);

        if ($user === null) {
            $this->respondWithError(404, 'User not found');
            return;
        }

        $this->respond($user);
    }

    public function delete(string $id): void
    {
        $token = AuthMiddleware::requireAdmin();
        $adminId = (int) $token->data->id;
        $targetId = (int) $id;

        if ($adminId === $targetId) {
            $this->respondWithError(422, 'Cannot delete own account');
            return;
        }

        $user = $this->repository->findById($targetId);

        if ($user === null) {
            $this->respondWithError(404, 'User not found');
            return;
        }

        $this->repository->delete($targetId);
        $this->respond(['message' => 'User deleted']);
    }

    public function resetPassword(string $id): void
    {
        $token = AuthMiddleware::requireAdmin();
        $targetId = (int) $id;

        $user = $this->repository->findById($targetId);

        if ($user === null) {
            $this->respondWithError(404, 'User not found');
            return;
        }

        $tempPassword = bin2hex(random_bytes(6));
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $this->repository->updatePassword($targetId, $hash);

        $this->respond(['temporary_password' => $tempPassword]);
    }

    public function bulkUpdateRole(): void
    {
        $token = AuthMiddleware::requireAdmin();
        $adminId = (int) $token->data->id;

        $body = $this->getRequestBody();

        if ($body === null || !isset($body->ids) || !isset($body->role)) {
            $this->respondWithError(400, 'ids and role are required');
            return;
        }

        $role = (int) $body->role;

        if ($role !== 0 && $role !== 1) {
            $this->respondWithError(422, 'Role must be 0 or 1');
            return;
        }

        $ids = array_map('intval', (array) $body->ids);
        $ids = array_filter($ids, fn(int $id) => $id !== $adminId);

        $affected = $this->repository->bulkUpdateRole($ids, $role);
        $this->respond(['affected' => $affected]);
    }

    public function bulkDelete(): void
    {
        $token = AuthMiddleware::requireAdmin();
        $adminId = (int) $token->data->id;

        $body = $this->getRequestBody();

        if ($body === null || !isset($body->ids)) {
            $this->respondWithError(400, 'ids is required');
            return;
        }

        $ids = array_map('intval', (array) $body->ids);
        $ids = array_filter($ids, fn(int $id) => $id !== $adminId);

        $affected = $this->repository->bulkDelete($ids);
        $this->respond(['affected' => $affected]);
    }
}
