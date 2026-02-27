<?php

declare(strict_types=1);

namespace Controllers;

use Services\UserService;

class AuthController extends Controller
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function login(): void
    {
        $body = $this->getRequestBody();

        if ($body === null || !isset($body->email) || !isset($body->password)) {
            $this->respondWithError(400, 'Email and password are required');
            return;
        }

        $result = $this->service->login($body->email, $body->password);

        if ($result === null) {
            $this->respondWithError(401, 'Invalid credentials');
            return;
        }

        $this->respond($result);
    }

    public function register(): void
    {
        $body = $this->getRequestBody();

        if ($body === null || !isset($body->username) || !isset($body->email) || !isset($body->password)) {
            $this->respondWithError(400, 'Username, email, and password are required');
            return;
        }

        if (strlen($body->password) < 6) {
            $this->respondWithError(422, 'Password must be at least 6 characters');
            return;
        }

        $result = $this->service->register($body->username, $body->email, $body->password);

        if ($result === null) {
            $this->respondWithError(422, 'Email is already registered');
            return;
        }

        $this->respondWithCode(201, $result);
    }

    private function respondWithCode(int $httpCode, mixed $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpCode);
        echo json_encode($data);
    }
}
