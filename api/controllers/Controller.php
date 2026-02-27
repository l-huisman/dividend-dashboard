<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Controller
{
    protected function checkForJwt(): ?object
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->respondWithError(401, 'No token provided');
            return null;
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $parts = explode(' ', $authHeader);

        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            $this->respondWithError(401, 'Invalid authorization header');
            return null;
        }

        $jwt = $parts[1];
        $secretKey = getenv('JWT_SECRET') ?: 'dividend-dashboard-secret-key';

        try {
            return JWT::decode($jwt, new Key($secretKey, 'HS256'));
        } catch (Exception $e) {
            $this->respondWithError(401, $e->getMessage());
            return null;
        }
    }

    protected function respond(mixed $data): void
    {
        $this->respondWithCode(200, $data);
    }

    protected function respondWithError(int $httpCode, string $message): void
    {
        $this->respondWithCode($httpCode, ['error' => $message]);
    }

    private function respondWithCode(int $httpCode, mixed $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpCode);
        echo json_encode($data);
    }

    protected function getRequestBody(): ?object
    {
        $json = file_get_contents('php://input');

        if ($json === false || $json === '') {
            return null;
        }

        $data = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return is_object($data) ? $data : null;
    }
}
