<?php

declare(strict_types=1);

namespace Services;

use Firebase\JWT\JWT;
use Models\User;
use Repositories\UserRepository;

class UserService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    /**
     * @return array{token: string, user: array<string, mixed>}|null
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->repository->findByEmail($email);

        if ($user === null || !password_verify($password, $user->password_hash)) {
            return null;
        }

        return [
            'token' => $this->generateToken($user),
            'user' => $this->sanitizeUser($user),
        ];
    }

    /**
     * @return array{token: string, user: array<string, mixed>}|null
     */
    public function register(string $username, string $email, string $password): ?array
    {
        $existing = $this->repository->findByEmail($email);

        if ($existing !== null) {
            return null;
        }

        $user = $this->repository->create($username, $email, $password);

        return [
            'token' => $this->generateToken($user),
            'user' => $this->sanitizeUser($user),
        ];
    }

    private function generateToken(User $user): string
    {
        $secretKey = getenv('JWT_SECRET') ?: 'dividend-dashboard-secret-key';

        $payload = [
            'iss' => 'dividend-dashboard',
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24),
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ];
    }
}
