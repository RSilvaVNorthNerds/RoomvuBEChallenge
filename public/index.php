<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controllers\UserController;
use App\Controllers\TransactionController;
use App\Controllers\ReportingController;
use App\Config\Database;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\TransactionService;
use App\Services\UserService;
use App\Services\ReportingService;

$request = Request::createFromGlobals();
$response = null;

// Create database instance
$database = Database::getInstance();
$database->createTables();

// Create repositories
$transactionRepository = new TransactionRepository($database);
$userRepository = new UserRepository($database);

// Create services
$transactionService = new TransactionService($transactionRepository, $userRepository);
$userService = new UserService($userRepository);
$reportingService = new ReportingService($transactionService);

// Create controllers
$userController = new UserController($userService);
$transactionController = new TransactionController($transactionService, $userService);
$reportingController = new ReportingController($reportingService);

$method = $request->getMethod();
$path = $request->getPathInfo();

$routes = [
    'GET' => [
        '/generate-user-daily-report' => [$reportingController, 'generateUserDailyReport'],
        '/generate-global-daily-report' => [$reportingController, 'generateGlobalDailyReport'],
    ],
    'POST' => [
        '/create-transaction' => [$transactionController, 'createTransaction'],
        '/create-user' => [$userController, 'createUser'],
        '/populate-users' => [$userController, 'populateFakeUsers'],
    ],
    "DELETE" => [
        '/delete-transaction' => [$transactionController, 'archiveTransaction'],
    ],
];

$route = $routes[$method][$path] ?? null;

if ($route) {
    [$controller, $methodName] = $route;
    $response = $controller->$methodName($request);
} else {
    $response = new Response(json_encode(['error' => 'Not Found']), Response::HTTP_NOT_FOUND, [
        'Content-Type' => 'application/json'
    ]);
}

$response->send();

















