<?php

declare(strict_types=1);

namespace Repositories;

use Models\Holding;
use Models\Stock;
use PDO;

class HoldingRepository extends Repository
{
    public function getAllForUser(int $userId, ?string $sort = null, ?string $direction = null): array
    {
        $allowed = ['ticker', 'name', 'shares', 'invested', 'dividend_yield', 'sector'];
        $sort = in_array($sort, $allowed) ? $sort : 'ticker';
        $direction = strtolower($direction ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        $orderColumn = match ($sort) {
            'ticker' => 's.ticker',
            'name' => 's.name',
            'shares' => 'h.shares',
            'invested' => 'h.invested',
            'dividend_yield' => 's.dividend_yield',
            'sector' => 's.sector',
            default => 's.ticker',
        };

        $stmt = $this->connection->prepare(
            "SELECT h.*, s.id as s_id, s.ticker, s.name as s_name, s.sector, s.price,
                    s.dividend_per_share, s.dividend_yield, s.ex_dividend_date, s.pay_date,
                    s.frequency, s.last_fetched_at
             FROM holdings h
             JOIN stocks s ON h.stock_id = s.id
             WHERE h.user_id = :user_id
             ORDER BY {$orderColumn} {$direction}"
        );
        $stmt->execute(['user_id' => $userId]);

        $holdings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $holdings[] = $this->rowToHolding($row);
        }

        return $holdings;
    }

    public function getOneForUser(int $userId, int $id): ?Holding
    {
        $stmt = $this->connection->prepare(
            "SELECT h.*, s.id as s_id, s.ticker, s.name as s_name, s.sector, s.price,
                    s.dividend_per_share, s.dividend_yield, s.ex_dividend_date, s.pay_date,
                    s.frequency, s.last_fetched_at
             FROM holdings h
             JOIN stocks s ON h.stock_id = s.id
             WHERE h.id = :id AND h.user_id = :user_id"
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->rowToHolding($row);
    }

    public function create(Holding $holding): Holding
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO holdings (user_id, stock_id, shares, invested, bought_on)
             VALUES (:user_id, :stock_id, :shares, :invested, :bought_on)'
        );
        $stmt->execute([
            'user_id' => $holding->user_id,
            'stock_id' => $holding->stock_id,
            'shares' => $holding->shares,
            'invested' => $holding->invested,
            'bought_on' => $holding->bought_on,
        ]);

        $id = (int) $this->connection->lastInsertId();

        return $this->getOneForUser($holding->user_id, $id);
    }

    public function update(Holding $holding, int $id, int $userId): ?Holding
    {
        $stmt = $this->connection->prepare(
            'UPDATE holdings SET shares = :shares, invested = :invested, bought_on = :bought_on
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'shares' => $holding->shares,
            'invested' => $holding->invested,
            'bought_on' => $holding->bought_on,
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $this->getOneForUser($userId, $id);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->connection->prepare('DELETE FROM holdings WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    private function rowToHolding(array $row): Holding
    {
        $holding = new Holding();
        $holding->id = (int) $row['id'];
        $holding->user_id = (int) $row['user_id'];
        $holding->stock_id = (int) $row['stock_id'];
        $holding->shares = (float) $row['shares'];
        $holding->invested = (float) $row['invested'];
        $holding->bought_on = $row['bought_on'];
        $holding->created_at = $row['created_at'];
        $holding->updated_at = $row['updated_at'];

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
        $holding->stock = $stock;

        return $holding;
    }
}
