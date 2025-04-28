<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controllers\UserController;
use App\Controllers\TransactionController;
use App\Controllers\ReportingController;
use App\config\Database;

$request = Request::createFromGlobals();
$response = null;

Database::getInstance()->createTables();

$method = $request->getMethod();
$path = $request->getPathInfo();

$routes = [
    'GET' => [
        '/generate-user-daily-report' => [ReportingController::class, 'generateUserDailyReport'],
    ],
    'POST' => [
        '/create-transaction' => [TransactionController::class, 'createTransaction'],
        '/create-user' => [UserController::class, 'createUser'],
        '/populate-users' => [UserController::class, 'populateFakeUsers'],
    ],
    "DELETE" => [
        '/delete-transaction' => [TransactionController::class, 'archiveTransaction'],
    ],
];

$route = $routes[$method][$path] ?? null;

if ($route) {
    [$controllerClass, $methodName] = $route;

    $controller = new $controllerClass();

    $response = $controller->$methodName($request);
} else {
    $response = new Response(json_encode(['error' => 'Not Found']), Response::HTTP_NOT_FOUND, [
        'Content-Type' => 'application/json'
    ]);
}

$response->send();

















