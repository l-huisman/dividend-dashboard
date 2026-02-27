<?php

declare(strict_types=1);

namespace Services;

use Models\Holding;
use Repositories\HoldingRepository;
use Repositories\StockRepository;

class HoldingService
{
    private HoldingRepository $holdingRepository;
    private StockRepository $stockRepository;

    public function __construct()
    {
        $this->holdingRepository = new HoldingRepository();
        $this->stockRepository = new StockRepository();
    }

    public function getAllForUser(int $userId, ?string $sort, ?string $direction): array
    {
        return $this->holdingRepository->getAllForUser($userId, $sort, $direction);
    }

    public function getOneForUser(int $userId, int $id): ?Holding
    {
        return $this->holdingRepository->getOneForUser($userId, $id);
    }

    public function create(int $userId, int $stockId, float $shares, float $invested, ?string $boughtOn): ?Holding
    {
        $stock = $this->stockRepository->getOne($stockId);

        if ($stock === null) {
            return null;
        }

        $holding = new Holding();
        $holding->user_id = $userId;
        $holding->stock_id = $stockId;
        $holding->shares = $shares;
        $holding->invested = $invested;
        $holding->bought_on = $boughtOn;

        return $this->holdingRepository->create($holding);
    }

    public function update(int $userId, int $id, float $shares, float $invested, ?string $boughtOn): ?Holding
    {
        $existing = $this->holdingRepository->getOneForUser($userId, $id);

        if ($existing === null) {
            return null;
        }

        $holding = new Holding();
        $holding->shares = $shares;
        $holding->invested = $invested;
        $holding->bought_on = $boughtOn;

        return $this->holdingRepository->update($holding, $id, $userId);
    }

    public function delete(int $userId, int $id): bool
    {
        $existing = $this->holdingRepository->getOneForUser($userId, $id);

        if ($existing === null) {
            return false;
        }

        $this->holdingRepository->delete($id, $userId);
        return true;
    }

    public function importCsv(int $userId, string $csvContent): array
    {
        $lines = explode("\n", trim($csvContent));
        $imported = 0;
        $skipped = 0;
        $unknown = [];

        // Skip header line
        array_shift($lines);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Parse CSV fields (Trading 212 format)
            $fields = str_getcsv($line);

            if (count($fields) < 6) {
                $skipped++;
                continue;
            }

            $ticker = trim($fields[0], '"');
            $invested = (float) str_replace(',', '.', trim($fields[2], '"'));
            $shares = (float) str_replace(',', '.', trim($fields[5], '"'));

            if ($ticker === '' || $ticker === 'Total' || $shares <= 0) {
                $skipped++;
                continue;
            }

            // Look up stock by ticker
            $stock = $this->stockRepository->getByTicker($ticker);

            if ($stock === null) {
                $unknown[] = $ticker;
                $skipped++;
                continue;
            }

            // Create holding (or skip if duplicate)
            try {
                $holding = new Holding();
                $holding->user_id = $userId;
                $holding->stock_id = $stock->id;
                $holding->shares = $shares;
                $holding->invested = $invested;

                $this->holdingRepository->create($holding);
                $imported++;
            } catch (\PDOException) {
                // Duplicate key or other constraint violation
                $skipped++;
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'unknown' => $unknown,
        ];
    }
}
