<?php

declare(strict_types=1);

namespace Services;

use Models\Stock;
use Repositories\StockRepository;

class StockService
{
    private StockRepository $repository;
    private YahooFinanceService $yahoo;

    public function __construct()
    {
        $this->repository = new StockRepository();
        $this->yahoo = new YahooFinanceService();
    }

    public function getAll(int $page, int $limit, ?string $search, ?string $sector, string $sort, string $direction): array
    {
        $stocks = $this->repository->getAll($page, $limit, $search, $sector, $sort, $direction);
        $total = $this->repository->count($search, $sector);

        return [
            'data' => $stocks,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        ];
    }

    public function getOne(int $id): ?Stock
    {
        return $this->repository->getOne($id);
    }

    public function refresh(string $ticker): ?Stock
    {
        $stock = $this->repository->getByTicker($ticker);

        if ($stock === null) {
            return null;
        }

        // Only re-fetch if older than 24 hours
        if ($stock->last_fetched_at !== null) {
            $lastFetched = strtotime($stock->last_fetched_at);
            if ($lastFetched !== false && (time() - $lastFetched) < 86400) {
                return $stock;
            }
        }

        $quote = $this->yahoo->fetchQuote($ticker);

        if ($quote === null) {
            return $stock;
        }

        $stock->price = $quote['price'];
        $stock->dividend_per_share = $quote['dividend_per_share'];
        $stock->dividend_yield = $quote['dividend_yield'];

        if ($quote['ex_dividend_date'] !== null) {
            $stock->ex_dividend_date = $quote['ex_dividend_date'];
        }

        if ($quote['sector'] !== '') {
            $stock->sector = $quote['sector'];
        }

        return $this->repository->update($stock, $stock->id);
    }

    public function create(Stock $stock): Stock
    {
        return $this->repository->create($stock);
    }

    public function update(Stock $stock, int $id): ?Stock
    {
        $existing = $this->repository->getOne($id);

        if ($existing === null) {
            return null;
        }

        return $this->repository->update($stock, $id);
    }

    public function delete(int $id): bool
    {
        $existing = $this->repository->getOne($id);

        if ($existing === null) {
            return false;
        }

        $this->repository->delete($id);
        return true;
    }
}
