<?php

declare(strict_types=1);

namespace Models;

class Stock
{
    public int $id = 0;
    public string $ticker = '';
    public string $name = '';
    public string $sector = '';
    public float $price = 0.0;
    public float $dividend_per_share = 0.0;
    public float $dividend_yield = 0.0;
    public ?string $ex_dividend_date = null;
    public ?string $pay_date = null;
    public string $frequency = 'quarterly';
    public ?string $last_fetched_at = null;
    public string $created_at = '';
    public string $updated_at = '';
    /** @var int[] */
    public array $payment_months = [];
}
