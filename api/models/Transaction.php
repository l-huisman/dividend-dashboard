<?php

declare(strict_types=1);

namespace Models;

class Transaction
{
    public int $id = 0;
    public int $user_id = 0;
    public int $stock_id = 0;
    public string $type = 'buy';  // 'buy' or 'sell'
    public float $shares = 0.0;
    public float $price_per_share = 0.0;
    public float $total_amount = 0.0;
    public ?string $executed_at = null;
    public string $created_at = '';
    public ?Stock $stock = null;
}
