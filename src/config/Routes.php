<?php

use App\Controllers\ReportingController;
use App\Controllers\TransactionController;
use App\Controllers\UserController;

return [
    'GET' => [
        '/generate-user-daily-report' => [ReportingController::class, 'generateUserDailyReport'],
        '/generate-global-daily-report' => [ReportingController::class, 'generateGlobalDailyReport'],
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

