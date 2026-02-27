<?php

declare(strict_types=1);

namespace Services;

use DateTime;
use Models\Holding;

class PortfolioService
{
    private const EUR_USD = 1.18;

    private const MONTH_NAMES = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ];

    /**
     * Convert a USD amount to EUR.
     */
    private function toEur(float $usd): float
    {
        return $usd / self::EUR_USD;
    }

    /**
     * Compute portfolio summary from holdings.
     *
     * @param Holding[] $holdings
     */
    public function computeSummary(array $holdings): array
    {
        $totalInvested = 0.0;
        $totalValue = 0.0;
        $totalAnnualDividend = 0.0;

        foreach ($holdings as $holding) {
            $stock = $holding->stock;
            $totalInvested += $holding->invested;
            $totalValue += $holding->shares * $stock->price;
            $totalAnnualDividend += $holding->shares * $stock->dividend_per_share;
        }

        $totalInvestedEur = $this->toEur($totalInvested);
        $totalValueEur = $this->toEur($totalValue);
        $totalAnnualDividendEur = $this->toEur($totalAnnualDividend);
        $totalGainEur = $totalValueEur - $totalInvestedEur;
        $totalGainPct = $totalInvestedEur > 0 ? $totalGainEur / $totalInvestedEur : 0.0;
        $weightedYield = $totalValueEur > 0 ? $totalAnnualDividendEur / $totalValueEur : 0.0;
        $monthlyDividend = $totalAnnualDividendEur / 12;
        $dailyDividend = $totalAnnualDividendEur / 365;

        return [
            'total_invested' => round($totalInvestedEur, 2),
            'total_value' => round($totalValueEur, 2),
            'total_annual_dividend' => round($totalAnnualDividendEur, 2),
            'total_gain' => round($totalGainEur, 2),
            'total_gain_pct' => round($totalGainPct, 4),
            'weighted_yield' => round($weightedYield, 4),
            'monthly_dividend' => round($monthlyDividend, 2),
            'daily_dividend' => round($dailyDividend, 2),
            'holding_count' => count($holdings),
        ];
    }

    /**
     * Compute sector breakdown by value and by dividend.
     *
     * @param Holding[] $holdings
     */
    public function computeSectors(array $holdings): array
    {
        $byValue = [];
        $byDividend = [];

        foreach ($holdings as $holding) {
            $stock = $holding->stock;
            $sector = $stock->sector ?: 'Unknown';
            $valueEur = $this->toEur($holding->shares * $stock->price);
            $dividendEur = $this->toEur($holding->shares * $stock->dividend_per_share);

            $byValue[$sector] = ($byValue[$sector] ?? 0.0) + $valueEur;
            $byDividend[$sector] = ($byDividend[$sector] ?? 0.0) + $dividendEur;
        }

        $byValueArray = [];
        foreach ($byValue as $name => $value) {
            $byValueArray[] = ['name' => $name, 'value' => round($value, 2)];
        }
        usort($byValueArray, fn(array $a, array $b) => $b['value'] <=> $a['value']);

        $byDividendArray = [];
        foreach ($byDividend as $name => $value) {
            $byDividendArray[] = ['name' => $name, 'value' => round($value, 2)];
        }
        usort($byDividendArray, fn(array $a, array $b) => $b['value'] <=> $a['value']);

        return [
            'by_value' => $byValueArray,
            'by_dividend' => $byDividendArray,
        ];
    }

    /**
     * Compute dividend calendar: monthly income, upcoming dividends, investment windows.
     *
     * @param Holding[] $holdings
     */
    public function computeCalendar(array $holdings): array
    {
        return [
            'monthly_income' => $this->computeMonthlyIncome($holdings),
            'upcoming_dividends' => $this->computeUpcomingDividends($holdings),
            'investment_windows' => $this->computeInvestmentWindows($holdings),
        ];
    }

    /**
     * Compute monthly dividend income across all 12 months.
     *
     * @param Holding[] $holdings
     */
    private function computeMonthlyIncome(array $holdings): array
    {
        $monthlyTotals = array_fill(0, 12, 0.0);

        foreach ($holdings as $holding) {
            $stock = $holding->stock;
            $paymentMonths = $stock->payment_months;
            $numPayments = count($paymentMonths);

            if ($numPayments === 0) {
                continue;
            }

            $perPaymentUsd = ($holding->shares * $stock->dividend_per_share) / $numPayments;

            foreach ($paymentMonths as $monthIndex) {
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $monthlyTotals[$monthIndex] += $perPaymentUsd;
                }
            }
        }

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $result[] = [
                'month' => self::MONTH_NAMES[$i],
                'income' => round($this->toEur($monthlyTotals[$i]), 2),
            ];
        }

        return $result;
    }

    /**
     * Calculate days between today and a target date string.
     */
    private function daysUntil(string $dateStr): int
    {
        $today = new DateTime('today');
        $target = new DateTime($dateStr);
        $diff = $today->diff($target);

        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * Compute upcoming dividend events sorted by nearest event date.
     *
     * @param Holding[] $holdings
     */
    private function computeUpcomingDividends(array $holdings): array
    {
        $upcoming = [];

        foreach ($holdings as $holding) {
            $stock = $holding->stock;

            if ($stock->ex_dividend_date === null && $stock->pay_date === null) {
                continue;
            }

            $daysUntilEx = $stock->ex_dividend_date !== null
                ? $this->daysUntil($stock->ex_dividend_date)
                : PHP_INT_MAX;

            $daysUntilPay = $stock->pay_date !== null
                ? $this->daysUntil($stock->pay_date)
                : PHP_INT_MAX;

            $numPayments = count($stock->payment_months);
            if ($numPayments === 0) {
                $numPayments = 4; // default quarterly
            }

            $perPaymentEur = $this->toEur(
                ($holding->shares * $stock->dividend_per_share) / $numPayments
            );

            // Sort key: nearest non-negative event date
            $sortDays = PHP_INT_MAX;
            if ($daysUntilEx >= 0 && $daysUntilEx < $sortDays) {
                $sortDays = $daysUntilEx;
            }
            if ($daysUntilPay >= 0 && $daysUntilPay < $sortDays) {
                $sortDays = $daysUntilPay;
            }

            $upcoming[] = [
                'ticker' => $stock->ticker,
                'name' => $stock->name,
                'ex_dividend_date' => $stock->ex_dividend_date,
                'pay_date' => $stock->pay_date,
                'days_until_ex' => $daysUntilEx === PHP_INT_MAX ? null : $daysUntilEx,
                'days_until_pay' => $daysUntilPay === PHP_INT_MAX ? null : $daysUntilPay,
                'per_payment' => round($perPaymentEur, 2),
                '_sort_days' => $sortDays,
            ];
        }

        usort($upcoming, fn(array $a, array $b) => $a['_sort_days'] <=> $b['_sort_days']);

        // Remove internal sort key
        return array_map(function (array $item) {
            unset($item['_sort_days']);
            return $item;
        }, $upcoming);
    }

    /**
     * Calculate 1 business day before a given date (skip weekends backwards).
     */
    private function businessDayBefore(DateTime $date): DateTime
    {
        $buyBy = clone $date;
        $buyBy->modify('-1 day');

        // Skip weekends backwards: 0 = Sunday, 6 = Saturday
        while ((int) $buyBy->format('w') === 0 || (int) $buyBy->format('w') === 6) {
            $buyBy->modify('-1 day');
        }

        return $buyBy;
    }

    /**
     * Compute investment windows: stocks grouped by nearby ex-dividend dates.
     *
     * @param Holding[] $holdings
     */
    private function computeInvestmentWindows(array $holdings): array
    {
        $today = new DateTime('today');
        $candidates = [];

        foreach ($holdings as $holding) {
            $stock = $holding->stock;

            if ($stock->ex_dividend_date === null) {
                continue;
            }

            $exDate = new DateTime($stock->ex_dividend_date);
            $daysUntilEx = $this->daysUntil($stock->ex_dividend_date);

            if ($daysUntilEx < 1) {
                continue;
            }

            $buyBy = $this->businessDayBefore($exDate);

            $numPayments = count($stock->payment_months);
            if ($numPayments === 0) {
                $numPayments = 4;
            }

            $perPaymentEur = $this->toEur(
                ($holding->shares * $stock->dividend_per_share) / $numPayments
            );

            $candidates[] = [
                'ticker' => $stock->ticker,
                'ex_div' => $stock->ex_dividend_date,
                'buy_by' => $buyBy->format('Y-m-d'),
                'buy_by_ts' => $buyBy->getTimestamp(),
                'per_payment' => round($perPaymentEur, 2),
                'frequency' => $stock->frequency,
            ];
        }

        // Sort candidates by buy_by date
        usort($candidates, fn(array $a, array $b) => $a['buy_by_ts'] <=> $b['buy_by_ts']);

        // Group stocks with buy_by dates within 7 days of each other
        $windows = [];
        $used = array_fill(0, count($candidates), false);

        for ($i = 0; $i < count($candidates); $i++) {
            if ($used[$i]) {
                continue;
            }

            $group = [$candidates[$i]];
            $used[$i] = true;
            $earliestBuyByTs = $candidates[$i]['buy_by_ts'];

            for ($j = $i + 1; $j < count($candidates); $j++) {
                if ($used[$j]) {
                    continue;
                }

                $daysDiff = abs($candidates[$j]['buy_by_ts'] - $earliestBuyByTs) / 86400;

                if ($daysDiff <= 7) {
                    $group[] = $candidates[$j];
                    $used[$j] = true;
                }
            }

            // Window buy_by = earliest buy_by in the group
            $windowBuyBy = $group[0]['buy_by'];
            $totalDividend = 0.0;
            $stocks = [];

            foreach ($group as $item) {
                $totalDividend += $item['per_payment'];
                $stocks[] = [
                    'ticker' => $item['ticker'],
                    'ex_div' => $item['ex_div'],
                    'per_payment' => $item['per_payment'],
                    'frequency' => $item['frequency'],
                ];
            }

            $daysUntilWindow = $this->daysUntil($windowBuyBy);

            $windows[] = [
                'buy_by' => $windowBuyBy,
                'days' => $daysUntilWindow,
                'total_dividend' => round($totalDividend, 2),
                'stocks' => $stocks,
            ];
        }

        // Sort by total_dividend descending
        usort($windows, fn(array $a, array $b) => $b['total_dividend'] <=> $a['total_dividend']);

        // Return top 5
        return array_slice($windows, 0, 5);
    }

    /**
     * DRIP growth projection.
     *
     * @return array<int, array{year: int, label: string, portfolio_value: int, total_contributed: int, annual_dividends: float, monthly_dividends: float, yield_on_cost: float}>
     */
    public function projectGrowth(
        float $startValueEur,
        float $monthlyContribEur,
        float $weightedYield,
        float $divGrowthRate,
        float $priceGrowthRate,
        int $years,
    ): array {
        $data = [];
        $portfolioValue = $startValueEur;
        $totalContributed = $startValueEur;
        $annualDividends = 0.0;

        for ($y = 0; $y <= $years; $y++) {
            $currentYield = $weightedYield * pow(1 + $divGrowthRate, $y);
            $annualDividends = $portfolioValue * $currentYield;

            $data[] = [
                'year' => $y,
                'label' => "Year {$y}",
                'portfolio_value' => (int) round($portfolioValue),
                'total_contributed' => (int) round($totalContributed),
                'annual_dividends' => round($annualDividends * 100) / 100,
                'monthly_dividends' => round(($annualDividends / 12) * 100) / 100,
                'yield_on_cost' => $totalContributed > 0
                    ? round($annualDividends / $totalContributed, 4)
                    : 0.0,
            ];

            if ($y < $years) {
                for ($m = 0; $m < 12; $m++) {
                    $monthDiv = $portfolioValue * ($currentYield / 12);
                    $portfolioValue += $monthDiv; // DRIP reinvestment
                    $portfolioValue *= (1 + $priceGrowthRate / 12); // stock price growth
                    $portfolioValue += $monthlyContribEur;
                    $totalContributed += $monthlyContribEur;
                }
            }
        }

        return $data;
    }
}
