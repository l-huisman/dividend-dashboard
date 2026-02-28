<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Models\Stock;
use Services\StockService;

class StockController extends Controller
{
    private StockService $service;

    public function __construct()
    {
        $this->service = new StockService();
    }

    public function getAll(): void
    {
        AuthMiddleware::requireAuth();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
        $search = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : null;
        $sector = isset($_GET['sector']) && $_GET['sector'] !== '' ? $_GET['sector'] : null;
        $sort = $_GET['sort'] ?? 'ticker';
        $direction = strtolower($_GET['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $result = $this->service->getAll($page, $limit, $search, $sector, $sort, $direction);
        $this->respond($result);
    }

    public function getOne(string $id): void
    {
        AuthMiddleware::requireAuth();

        $stock = $this->service->getOne((int) $id);

        if ($stock === null) {
            $this->respondWithError(404, 'Stock not found');
            return;
        }

        $this->respond($stock);
    }

    public function refresh(string $ticker): void
    {
        AuthMiddleware::requireAuth();

        $stock = $this->service->refresh(strtoupper($ticker));

        if ($stock === null) {
            $this->respondWithError(404, 'Stock not found');
            return;
        }

        $this->respond($stock);
    }

    public function lookup(string $ticker): void
    {
        AuthMiddleware::requireAuth();

        $yahoo = new \Services\YahooFinanceService();
        $quote = $yahoo->fetchQuote(strtoupper($ticker));

        if ($quote === null) {
            $this->respondWithError(404, 'Ticker not found on Yahoo Finance');
            return;
        }

        $this->respond($quote);
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $body = $this->getRequestBody();

        if ($body === null || !isset($body->ticker) || !isset($body->name)) {
            $this->respondWithError(400, 'Ticker and name are required');
            return;
        }

        $stock = new Stock();
        $stock->ticker = strtoupper($body->ticker);
        $stock->name = $body->name;
        $stock->sector = $body->sector ?? '';
        $stock->price = (float) ($body->price ?? 0);
        $stock->dividend_per_share = (float) ($body->dividend_per_share ?? 0);
        $stock->dividend_yield = (float) ($body->dividend_yield ?? 0);
        $stock->ex_dividend_date = $body->ex_dividend_date ?? null;
        $stock->pay_date = $body->pay_date ?? null;
        $stock->frequency = $body->frequency ?? 'quarterly';

        if (isset($body->payment_months) && is_array($body->payment_months)) {
            $stock->payment_months = array_map('intval', $body->payment_months);
        }

        $created = $this->service->create($stock);

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(201);
        echo json_encode($created);
    }

    public function update(string $id): void
    {
        AuthMiddleware::requireAdmin();

        $body = $this->getRequestBody();

        if ($body === null) {
            $this->respondWithError(400, 'Request body is required');
            return;
        }

        $stock = new Stock();
        $stock->ticker = isset($body->ticker) ? strtoupper($body->ticker) : '';
        $stock->name = $body->name ?? '';
        $stock->sector = $body->sector ?? '';
        $stock->price = (float) ($body->price ?? 0);
        $stock->dividend_per_share = (float) ($body->dividend_per_share ?? 0);
        $stock->dividend_yield = (float) ($body->dividend_yield ?? 0);
        $stock->ex_dividend_date = $body->ex_dividend_date ?? null;
        $stock->pay_date = $body->pay_date ?? null;
        $stock->frequency = $body->frequency ?? 'quarterly';

        if (isset($body->payment_months) && is_array($body->payment_months)) {
            $stock->payment_months = array_map('intval', $body->payment_months);
        }

        $updated = $this->service->update($stock, (int) $id);

        if ($updated === null) {
            $this->respondWithError(404, 'Stock not found');
            return;
        }

        $this->respond($updated);
    }

    public function delete(string $id): void
    {
        AuthMiddleware::requireAdmin();

        try {
            $deleted = $this->service->delete((int) $id);

            if (!$deleted) {
                $this->respondWithError(404, 'Stock not found');
                return;
            }

            $this->respond(['message' => 'Stock deleted']);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'constraint')) {
                $this->respondWithError(409, 'Cannot delete stock that has holdings or transactions');
                return;
            }
            throw $e;
        }
    }
}
