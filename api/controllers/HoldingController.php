<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Services\HoldingService;

class HoldingController extends Controller
{
    private HoldingService $service;

    public function __construct()
    {
        $this->service = new HoldingService();
    }

    public function getAll(): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $sort = $_GET['sort'] ?? null;
        $direction = $_GET['direction'] ?? null;

        $holdings = $this->service->getAllForUser((int) $token->data->id, $sort, $direction);
        $this->respond($holdings);
    }

    public function getOne(string $id): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $holding = $this->service->getOneForUser((int) $token->data->id, (int) $id);

        if ($holding === null) {
            $this->respondWithError(404, 'Holding not found');
            return;
        }

        $this->respond($holding);
    }

    public function create(): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $body = $this->getRequestBody();

        if ($body === null || !isset($body->stock_id) || !isset($body->shares)) {
            $this->respondWithError(400, 'stock_id and shares are required');
            return;
        }

        $holding = $this->service->create(
            (int) $token->data->id,
            (int) $body->stock_id,
            (float) $body->shares,
            (float) ($body->invested ?? 0),
            $body->bought_on ?? null
        );

        if ($holding === null) {
            $this->respondWithError(404, 'Stock not found');
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(201);
        echo json_encode($holding);
    }

    public function update(string $id): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $body = $this->getRequestBody();

        if ($body === null) {
            $this->respondWithError(400, 'Request body is required');
            return;
        }

        $holding = $this->service->update(
            (int) $token->data->id,
            (int) $id,
            (float) ($body->shares ?? 0),
            (float) ($body->invested ?? 0),
            $body->bought_on ?? null
        );

        if ($holding === null) {
            $this->respondWithError(404, 'Holding not found');
            return;
        }

        $this->respond($holding);
    }

    public function delete(string $id): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $deleted = $this->service->delete((int) $token->data->id, (int) $id);

        if (!$deleted) {
            $this->respondWithError(404, 'Holding not found');
            return;
        }

        $this->respond(['message' => 'Holding deleted']);
    }

    public function import(): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $csvContent = file_get_contents('php://input');

        if ($csvContent === false || $csvContent === '') {
            $this->respondWithError(400, 'CSV content is required');
            return;
        }

        $result = $this->service->importCsv((int) $token->data->id, $csvContent);
        $this->respond($result);
    }
}
