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

        $users = $this->repository->getAll($page, $limit);
        $total = $this->repository->count();

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
}
