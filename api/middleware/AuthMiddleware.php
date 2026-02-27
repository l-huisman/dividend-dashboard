<?php

declare(strict_types=1);

namespace Middleware;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public static function getToken(): ?object
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return null;
        }

        $parts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return null;
        }

        $secretKey = getenv('JWT_SECRET') ?: 'dividend-dashboard-secret-key';

        try {
            return JWT::decode($parts[1], new Key($secretKey, 'HS256'));
        } catch (Exception) {
            return null;
        }
    }

    public static function requireAuth(): object
    {
        $token = self::getToken();

        if ($token === null) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }

        return $token;
    }

    public static function requireAdmin(): object
    {
        $token = self::requireAuth();

        if (!isset($token->data->role) || (int) $token->data->role !== 1) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }

        return $token;
    }
}
