<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Repositories\StockRepository;
use Repositories\UserRepository;

class AdminController extends Controller
{
    public function stats(): void
    {
        AuthMiddleware::requireAdmin();

        $userRepo = new UserRepository();
        $stockRepo = new StockRepository();

        $this->respond([
            'total_users' => $userRepo->count(),
            'total_admins' => $userRepo->countByRole(1),
            'total_stocks' => $stockRepo->count(null, null),
        ]);
    }
}
