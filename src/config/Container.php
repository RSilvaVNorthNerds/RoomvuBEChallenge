<?php

declare(strict_types=1);

namespace App\Config;

use App\Config\Database;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\TransactionService;
use App\Services\UserService;
use App\Services\ReportingService;
use App\Controllers\UserController;
use App\Controllers\TransactionController;
use App\Controllers\ReportingController;

class Container
{
    private static ?Container $instance = null;
    private array $services = [];

    private function __construct()
    {
        $this->initializeServices();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeServices(): void
    {
        // Database
        $database = Database::getInstance();
        $database->createTables();
        
        $this->services[Database::class] = $database;

        // Repositories
        $this->services[TransactionRepository::class] = new TransactionRepository($database);
        $this->services[UserRepository::class] = new UserRepository($database);

        // Services
        $this->services[TransactionService::class] = new TransactionService(
            $this->services[TransactionRepository::class],
            $this->services[UserRepository::class]
        );
        $this->services[UserService::class] = new UserService($this->services[UserRepository::class]);
        $this->services[ReportingService::class] = new ReportingService($this->services[TransactionService::class]);

        // Controllers
        $this->services[UserController::class] = new UserController($this->services[UserService::class]);
        $this->services[TransactionController::class] = new TransactionController(
            $this->services[TransactionService::class],
            $this->services[UserService::class]
        );
        $this->services[ReportingController::class] = new ReportingController($this->services[ReportingService::class]);
    }

    public function get(string $serviceName): mixed
    {
        if (!isset($this->services[$serviceName])) {
            throw new \RuntimeException("Service {$serviceName} not found in container");
        }
        return $this->services[$serviceName];
    }
} 