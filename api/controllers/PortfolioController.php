<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Repositories\HoldingRepository;
use Repositories\StockRepository;
use Services\PortfolioService;

class PortfolioController extends Controller
{
    private PortfolioService $service;
    private HoldingRepository $holdingRepository;
    private StockRepository $stockRepository;

    public function __construct()
    {
        $this->service = new PortfolioService();
        $this->holdingRepository = new HoldingRepository();
        $this->stockRepository = new StockRepository();
    }

    /**
     * Authenticate and block admin users. Returns user ID or null if blocked.
     */
    private function authenticateUser(): ?int
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot view user portfolios');
            return null;
        }

        return (int) $token->data->id;
    }

    /**
     * Fetch holdings with payment_months populated on each stock.
     *
     * @return \Models\Holding[]
     */
    private function getHoldingsWithPaymentMonths(int $userId): array
    {
        $holdings = $this->holdingRepository->getAllForUser($userId);

        foreach ($holdings as $holding) {
            if ($holding->stock !== null) {
                $holding->stock->payment_months = $this->stockRepository->getPaymentMonths($holding->stock->id);
            }
        }

        return $holdings;
    }

    /**
     * GET /portfolio/summary
     */
    public function summary(): void
    {
        $userId = $this->authenticateUser();
        if ($userId === null) {
            return;
        }

        $holdings = $this->getHoldingsWithPaymentMonths($userId);
        $summary = $this->service->computeSummary($holdings);
        $this->respond($summary);
    }

    /**
     * GET /portfolio/sectors
     */
    public function sectors(): void
    {
        $userId = $this->authenticateUser();
        if ($userId === null) {
            return;
        }

        $holdings = $this->getHoldingsWithPaymentMonths($userId);
        $sectors = $this->service->computeSectors($holdings);
        $this->respond($sectors);
    }

    /**
     * GET /portfolio/calendar
     */
    public function calendar(): void
    {
        $userId = $this->authenticateUser();
        if ($userId === null) {
            return;
        }

        $holdings = $this->getHoldingsWithPaymentMonths($userId);
        $calendar = $this->service->computeCalendar($holdings);
        $this->respond($calendar);
    }

    /**
     * GET /portfolio/projection?monthly=100&years=20&divgrowth=5&pricegrowth=7
     */
    public function projection(): void
    {
        $userId = $this->authenticateUser();
        if ($userId === null) {
            return;
        }

        $monthlyContrib = (float) ($_GET['monthly'] ?? 100);
        $years = (int) ($_GET['years'] ?? 20);
        $divGrowthPct = (float) ($_GET['divgrowth'] ?? 5);
        $priceGrowthPct = (float) ($_GET['pricegrowth'] ?? 7);

        $divGrowthRate = $divGrowthPct / 100;
        $priceGrowthRate = $priceGrowthPct / 100;

        $holdings = $this->getHoldingsWithPaymentMonths($userId);
        $summary = $this->service->computeSummary($holdings);

        $startValueEur = (float) $summary['total_value'];
        $weightedYield = (float) $summary['weighted_yield'];

        $projection = $this->service->projectGrowth(
            $startValueEur,
            $monthlyContrib,
            $weightedYield,
            $divGrowthRate,
            $priceGrowthRate,
            $years,
        );

        $this->respond($projection);
    }
}
