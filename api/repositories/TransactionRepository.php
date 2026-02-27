<?php

declare(strict_types=1);

namespace Repositories;

use Models\Stock;
use Models\Transaction;
use PDO;

class TransactionRepository extends Repository
{
    /**
     * @return Transaction[]
     */
    public function getAllForUser(int $userId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT t.*, s.id as s_id, s.ticker, s.name as s_name, s.sector, s.price,
                    s.dividend_per_share, s.dividend_yield, s.ex_dividend_date, s.pay_date,
                    s.frequency, s.last_fetched_at
             FROM transactions t
             JOIN stocks s ON t.stock_id = s.id
             WHERE t.user_id = :user_id
             ORDER BY t.executed_at DESC, t.created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        $transactions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $transactions[] = $this->rowToTransaction($row);
        }

        return $transactions;
    }

    public function create(Transaction $transaction): Transaction
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO transactions (user_id, stock_id, type, shares, price_per_share, total_amount, executed_at)
             VALUES (:user_id, :stock_id, :type, :shares, :price_per_share, :total_amount, :executed_at)'
        );
        $stmt->execute([
            'user_id' => $transaction->user_id,
            'stock_id' => $transaction->stock_id,
            'type' => $transaction->type,
            'shares' => $transaction->shares,
            'price_per_share' => $transaction->price_per_share,
            'total_amount' => $transaction->total_amount,
            'executed_at' => $transaction->executed_at,
        ]);

        $transaction->id = (int) $this->connection->lastInsertId();
        return $transaction;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function rowToTransaction(array $row): Transaction
    {
        $tx = new Transaction();
        $tx->id = (int) $row['id'];
        $tx->user_id = (int) $row['user_id'];
        $tx->stock_id = (int) $row['stock_id'];
        $tx->type = $row['type'];
        $tx->shares = (float) $row['shares'];
        $tx->price_per_share = (float) $row['price_per_share'];
        $tx->total_amount = (float) $row['total_amount'];
        $tx->executed_at = $row['executed_at'];
        $tx->created_at = $row['created_at'];

        $stock = new Stock();
        $stock->id = (int) $row['s_id'];
        $stock->ticker = $row['ticker'];
        $stock->name = $row['s_name'];
        $stock->sector = $row['sector'];
        $stock->price = (float) $row['price'];
        $stock->dividend_per_share = (float) $row['dividend_per_share'];
        $stock->dividend_yield = (float) $row['dividend_yield'];
        $stock->ex_dividend_date = $row['ex_dividend_date'];
        $stock->pay_date = $row['pay_date'];
        $stock->frequency = $row['frequency'];
        $stock->last_fetched_at = $row['last_fetched_at'];
        $tx->stock = $stock;

        return $tx;
    }
}
