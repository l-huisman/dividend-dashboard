<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Services\HoldingService;
use Services\TransactionService;

class HoldingController extends Controller
{
    private HoldingService $service;
    private TransactionService $txService;

    public function __construct()
    {
        $this->service = new HoldingService();
        $this->txService = new TransactionService();
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

        $userId = (int) $token->data->id;
        $shares = (float) $body->shares;
        $invested = (float) ($body->invested ?? 0);
        $boughtOn = $body->bought_on ?? null;

        $holding = $this->service->create($userId, (int) $body->stock_id, $shares, $invested, $boughtOn);

        if ($holding === null) {
            $this->respondWithError(404, 'Stock not found');
            return;
        }

        // Record buy transaction
        $pricePerShare = $shares > 0 ? $invested / $shares : 0;
        $this->txService->recordBuy($userId, (int) $body->stock_id, $shares, $pricePerShare, $boughtOn);

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(201);
        echo json_encode($holding);
    }

    public function sell(string $id): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access holdings');
            return;
        }

        $body = $this->getRequestBody();

        if ($body === null || !isset($body->shares)) {
            $this->respondWithError(400, 'shares is required');
            return;
        }

        $userId = (int) $token->data->id;
        $sharesToSell = (float) $body->shares;

        $existing = $this->service->getOneForUser($userId, (int) $id);

        if ($existing === null) {
            $this->respondWithError(404, 'Holding not found');
            return;
        }

        if ($sharesToSell <= 0) {
            $this->respondWithError(400, 'Shares must be greater than 0');
            return;
        }

        if ($sharesToSell > $existing->shares + 0.00000001) {
            $this->respondWithError(400, 'Cannot sell more shares than owned');
            return;
        }

        // Record sell transaction at current market price
        $pricePerShare = $existing->stock->price ?? 0;
        $this->txService->recordSell($userId, $existing->stock_id, $sharesToSell, $pricePerShare);

        // Full sell: delete the holding
        if ($sharesToSell >= $existing->shares - 0.00000001) {
            $this->service->delete($userId, (int) $id);
            $this->respond(['message' => 'Holding sold completely']);
            return;
        }

        // Partial sell: reduce shares and proportionally reduce invested
        $fraction = $sharesToSell / $existing->shares;
        $newShares = round($existing->shares - $sharesToSell, 8);
        $newInvested = round($existing->invested * (1 - $fraction), 4);

        $updated = $this->service->update($userId, (int) $id, $newShares, $newInvested, $existing->bought_on);
        $this->respond($updated);
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
