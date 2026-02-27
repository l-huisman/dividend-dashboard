<?php

declare(strict_types=1);

namespace Services;

class YahooFinanceService
{
    private const CHART_URL = 'https://query1.finance.yahoo.com/v8/finance/chart/';
    private const SUMMARY_URL = 'https://query1.finance.yahoo.com/v10/finance/quoteSummary/';

    public function fetchQuote(string $ticker): ?array
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0\r\n",
                'timeout' => 10,
            ],
        ]);

        // Fetch chart data for price
        $chartUrl = self::CHART_URL . urlencode($ticker) . '?interval=1d&range=5d';
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

        // Fetch summary data for dividends and sector
        $summaryUrl = self::SUMMARY_URL . urlencode($ticker) . '?modules=summaryDetail,assetProfile';
        $summaryJson = @file_get_contents($summaryUrl, false, $context);

        $dividendPerShare = 0.0;
        $dividendYield = 0.0;
        $sector = '';
        $exDividendDate = null;

        if ($summaryJson !== false) {
            $summaryData = json_decode($summaryJson, true);
            $summary = $summaryData['quoteSummary']['result'][0] ?? [];

            $detail = $summary['summaryDetail'] ?? [];
            $dividendPerShare = (float) ($detail['dividendRate']['raw'] ?? 0);
            $dividendYield = (float) ($detail['dividendYield']['raw'] ?? 0);

            if (isset($detail['exDividendDate']['fmt'])) {
                $exDividendDate = $detail['exDividendDate']['fmt'];
            }

            $profile = $summary['assetProfile'] ?? [];
            $sector = $profile['sector'] ?? '';
        }

        return [
            'price' => $price,
            'dividend_per_share' => $dividendPerShare,
            'dividend_yield' => $dividendYield,
            'sector' => $sector,
            'ex_dividend_date' => $exDividendDate,
            'name' => $meta['shortName'] ?? $meta['symbol'] ?? $ticker,
        ];
    }
}
