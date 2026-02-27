<?php

declare(strict_types=1);

namespace Repositories;

use Models\Stock;
use PDO;

class StockRepository extends Repository
{
    /**
     * @return Stock[]
     */
    public function getAll(int $page, int $limit, ?string $search, ?string $sector, string $sort, string $direction): array
    {
        $allowed = ['ticker', 'name', 'price', 'dividend_yield', 'sector'];

        if (!in_array($sort, $allowed)) {
            $sort = 'ticker';
        }

        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        $sql = 'SELECT * FROM stocks WHERE 1=1';
        $params = [];

        if ($search !== null) {
            $sql .= ' AND (ticker LIKE :search OR name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($sector !== null) {
            $sql .= ' AND sector = :sector';
            $params['sector'] = $sector;
        }

        $sql .= " ORDER BY {$sort} {$direction}";
        $sql .= ' LIMIT :limit OFFSET :offset';

        $offset = ($page - 1) * $limit;

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $stocks = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stock = $this->rowToStock($row);
            $stock->payment_months = $this->getPaymentMonths($stock->id);
            $stocks[] = $stock;
        }

        return $stocks;
    }

    public function getOne(int $id): ?Stock
    {
        $stmt = $this->connection->prepare('SELECT * FROM stocks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $stock = $this->rowToStock($row);
        $stock->payment_months = $this->getPaymentMonths($stock->id);

        return $stock;
    }

    public function getByTicker(string $ticker): ?Stock
    {
        $stmt = $this->connection->prepare('SELECT * FROM stocks WHERE ticker = :ticker');
        $stmt->execute(['ticker' => $ticker]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $stock = $this->rowToStock($row);
        $stock->payment_months = $this->getPaymentMonths($stock->id);

        return $stock;
    }

    public function create(Stock $stock): Stock
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO stocks (ticker, name, sector, price, dividend_per_share, dividend_yield, ex_dividend_date, pay_date, frequency)
             VALUES (:ticker, :name, :sector, :price, :dividend_per_share, :dividend_yield, :ex_dividend_date, :pay_date, :frequency)'
        );

        $stmt->execute([
            'ticker' => $stock->ticker,
            'name' => $stock->name,
            'sector' => $stock->sector,
            'price' => $stock->price,
            'dividend_per_share' => $stock->dividend_per_share,
            'dividend_yield' => $stock->dividend_yield,
            'ex_dividend_date' => $stock->ex_dividend_date,
            'pay_date' => $stock->pay_date,
            'frequency' => $stock->frequency,
        ]);

        $id = (int) $this->connection->lastInsertId();

        if (!empty($stock->payment_months)) {
            $this->setPaymentMonths($id, $stock->payment_months);
        }

        return $this->getOne($id);
    }

    public function update(Stock $stock, int $id): Stock
    {
        $stmt = $this->connection->prepare(
            'UPDATE stocks SET
                ticker = :ticker,
                name = :name,
                sector = :sector,
                price = :price,
                dividend_per_share = :dividend_per_share,
                dividend_yield = :dividend_yield,
                ex_dividend_date = :ex_dividend_date,
                pay_date = :pay_date,
                frequency = :frequency,
                last_fetched_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'ticker' => $stock->ticker,
            'name' => $stock->name,
            'sector' => $stock->sector,
            'price' => $stock->price,
            'dividend_per_share' => $stock->dividend_per_share,
            'dividend_yield' => $stock->dividend_yield,
            'ex_dividend_date' => $stock->ex_dividend_date,
            'pay_date' => $stock->pay_date,
            'frequency' => $stock->frequency,
            'id' => $id,
        ]);

        if (!empty($stock->payment_months)) {
            $this->setPaymentMonths($id, $stock->payment_months);
        }

        return $this->getOne($id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare('DELETE FROM stocks WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function count(?string $search, ?string $sector): int
    {
        $sql = 'SELECT COUNT(*) FROM stocks WHERE 1=1';
        $params = [];

        if ($search !== null) {
            $sql .= ' AND (ticker LIKE :search OR name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($sector !== null) {
            $sql .= ' AND sector = :sector';
            $params['sector'] = $sector;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<int>
     */
    public function getPaymentMonths(int $stockId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT month FROM stock_payment_months WHERE stock_id = :stock_id ORDER BY month ASC'
        );
        $stmt->execute(['stock_id' => $stockId]);

        $months = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $months[] = (int) $row['month'];
        }

        return $months;
    }

    /**
     * @param list<int> $months
     */
    public function setPaymentMonths(int $stockId, array $months): void
    {
        $stmt = $this->connection->prepare('DELETE FROM stock_payment_months WHERE stock_id = :stock_id');
        $stmt->execute(['stock_id' => $stockId]);

        $stmt = $this->connection->prepare(
            'INSERT INTO stock_payment_months (stock_id, month) VALUES (:stock_id, :month)'
        );

        foreach ($months as $month) {
            $stmt->execute([
                'stock_id' => $stockId,
                'month' => (int) $month,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function rowToStock(array $row): Stock
    {
        $stock = new Stock();
        $stock->id = (int) $row['id'];
        $stock->ticker = $row['ticker'];
        $stock->name = $row['name'];
        $stock->sector = $row['sector'];
        $stock->price = (float) $row['price'];
        $stock->dividend_per_share = (float) $row['dividend_per_share'];
        $stock->dividend_yield = (float) $row['dividend_yield'];
        $stock->ex_dividend_date = $row['ex_dividend_date'];
        $stock->pay_date = $row['pay_date'];
        $stock->frequency = $row['frequency'];
        $stock->last_fetched_at = $row['last_fetched_at'];
        $stock->created_at = $row['created_at'];
        $stock->updated_at = $row['updated_at'];

        return $stock;
    }
}
