<?php

declare(strict_types=1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '0');

require __DIR__ . '/../vendor/autoload.php';

$router = new \Bramus\Router\Router();
$router->setBasePath('/');
$router->setNamespace('Controllers');

// Auth (public)
$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');

// Stocks (authenticated)
$router->get('/stocks', 'StockController@getAll');
$router->get('/stocks/(\d+)', 'StockController@getOne');
$router->get('/stocks/lookup/([A-Za-z.]+)', 'StockController@lookup');
$router->post('/stocks/refresh/([A-Za-z.]+)', 'StockController@refresh');
$router->post('/stocks', 'StockController@create');
$router->put('/stocks/(\d+)', 'StockController@update');
$router->delete('/stocks/(\d+)', 'StockController@delete');

// Holdings (authenticated, own user)
$router->get('/holdings', 'HoldingController@getAll');
$router->get('/holdings/(\d+)', 'HoldingController@getOne');
$router->post('/holdings', 'HoldingController@create');
$router->put('/holdings/(\d+)', 'HoldingController@update');
$router->delete('/holdings/(\d+)', 'HoldingController@delete');
$router->post('/holdings/(\d+)/sell', 'HoldingController@sell');
$router->post('/holdings/import', 'HoldingController@import');

// Transactions (authenticated, own user)
$router->get('/transactions', 'TransactionController@getAll');

// Admin (admin only)
$router->get('/admin/stats', 'AdminController@stats');

// Users (admin only)
$router->get('/users', 'UserController@getAll');
$router->put('/users/(\d+)', 'UserController@update');
$router->delete('/users/(\d+)', 'UserController@delete');
$router->put('/users/(\d+)/password', 'UserController@resetPassword');
$router->post('/users/bulk-role', 'UserController@bulkUpdateRole');
$router->post('/users/bulk-delete', 'UserController@bulkDelete');

// Portfolio (authenticated, computed)
$router->get('/portfolio/summary', 'PortfolioController@summary');
$router->get('/portfolio/sectors', 'PortfolioController@sectors');
$router->get('/portfolio/calendar', 'PortfolioController@calendar');
$router->get('/portfolio/projection', 'PortfolioController@projection');

$router->run();
