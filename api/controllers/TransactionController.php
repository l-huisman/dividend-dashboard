<?php

declare(strict_types=1);

namespace Controllers;

use Middleware\AuthMiddleware;
use Services\TransactionService;

class TransactionController extends Controller
{
    private TransactionService $service;

    public function __construct()
    {
        $this->service = new TransactionService();
    }

    public function getAll(): void
    {
        $token = AuthMiddleware::requireAuth();

        if ((int) $token->data->role === 1) {
            $this->respondWithError(403, 'Admin cannot access transactions');
            return;
        }

        $transactions = $this->service->getAllForUser((int) $token->data->id);
        $this->respond($transactions);
    }
}
