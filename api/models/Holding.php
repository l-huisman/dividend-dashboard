<?php

declare(strict_types=1);

namespace Models;

class Holding
{
    public int $id = 0;
    public int $user_id = 0;
    public int $stock_id = 0;
    public float $shares = 0.0;
    public float $invested = 0.0;
    public ?string $bought_on = null;
    public string $created_at = '';
    public string $updated_at = '';
    public ?Stock $stock = null;
}
