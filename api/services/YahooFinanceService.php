<?php

declare(strict_types=1);

namespace Services;

class YahooFinanceService
{
    private const CHART_URL = 'https://query1.finance.yahoo.com/v8/finance/chart/';

    /**
     * @return array{price: float, dividend_per_share: float, dividend_yield: float, sector: string, ex_dividend_date: string|null, name: string}|null
     */
    public function fetchQuote(string $ticker): ?array
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0\r\n",
                'timeout' => 10,
            ],
        ]);

        // Fetch chart data with 2 years of dividend events
        $chartUrl = self::CHART_URL . urlencode($ticker) . '?interval=1mo&range=2y&events=div';
        $chartJson = @file_get_contents($chartUrl, false, $context);

        if ($chartJson === false) {
            return null;
        }

        $chartData = json_decode($chartJson, true);

        if (!isset($chartData['chart']['result'][0])) {
            return null;
        }

        $result = $chartData['chart']['result'][0];
        $meta = $result['meta'] ?? [];
        $price = (float) ($meta['regularMarketPrice'] ?? 0);

        // Calculate annual dividend from dividend events in the last ~13 months
        $dividends = $result['events']['dividends'] ?? [];
        $dividendPerShare = 0.0;
        $exDividendDate = null;

        if (!empty($dividends)) {
            $oneYearAgo = time() - (13 * 30 * 86400);
            $recentDividends = [];

            foreach ($dividends as $div) {
                if ((int) $div['date'] >= $oneYearAgo) {
                    $recentDividends[] = $div;
                }
            }

            if (!empty($recentDividends)) {
                $dividendPerShare = array_sum(array_column($recentDividends, 'amount'));

                // Most recent dividend date as ex-dividend date
                usort($recentDividends, fn($a, $b) => $b['date'] <=> $a['date']);
                $exDividendDate = date('Y-m-d', (int) $recentDividends[0]['date']);
            }
        }

        $dividendYield = $price > 0 ? $dividendPerShare / $price : 0.0;

        return [
            'price' => $price,
            'dividend_per_share' => round($dividendPerShare, 4),
            'dividend_yield' => round($dividendYield, 6),
            'sector' => '',
            'ex_dividend_date' => $exDividendDate,
            'name' => $meta['shortName'] ?? $meta['symbol'] ?? $ticker,
        ];
    }
}
