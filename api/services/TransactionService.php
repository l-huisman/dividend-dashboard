<?php

declare(strict_types=1);

namespace Services;

use Models\Transaction;
use Repositories\TransactionRepository;

class TransactionService
{
    private TransactionRepository $repository;

    public function __construct()
    {
        $this->repository = new TransactionRepository();
    }

    /**
     * @return Transaction[]
     */
    public function getAllForUser(int $userId): array
    {
        return $this->repository->getAllForUser($userId);
    }

    public function recordBuy(int $userId, int $stockId, float $shares, float $pricePerShare, ?string $executedAt = null): Transaction
    {
        $tx = new Transaction();
        $tx->user_id = $userId;
        $tx->stock_id = $stockId;
        $tx->type = 'buy';
        $tx->shares = $shares;
        $tx->price_per_share = $pricePerShare;
        $tx->total_amount = $shares * $pricePerShare;
        $tx->executed_at = $executedAt ?? date('Y-m-d');

        return $this->repository->create($tx);
    }

    public function recordSell(int $userId, int $stockId, float $shares, float $pricePerShare, ?string $executedAt = null): Transaction
    {
        $tx = new Transaction();
        $tx->user_id = $userId;
        $tx->stock_id = $stockId;
        $tx->type = 'sell';
        $tx->shares = $shares;
        $tx->price_per_share = $pricePerShare;
        $tx->total_amount = $shares * $pricePerShare;
        $tx->executed_at = $executedAt ?? date('Y-m-d');

        return $this->repository->create($tx);
    }
}
