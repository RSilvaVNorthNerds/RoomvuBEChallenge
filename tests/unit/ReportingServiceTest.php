<?php

use App\Services\ReportingService;
use App\Services\TransactionService;
use Symfony\Component\Cache\Adapter\RedisAdapter;

beforeEach(function () {
    $this->transactionService = Mockery::mock(TransactionService::class);
    
    $this->redis = Mockery::mock('Redis');
    
    // Mock RedisAdapter::createConnection to return our mock
    $this->redisAdapter = Mockery::mock('overload:' . RedisAdapter::class);
    $this->redisAdapter->shouldReceive('createConnection')
        ->andReturn($this->redis);
    
    $this->reportingService = new ReportingService($this->transactionService);
});

afterEach(function () {
    Mockery::close();
});

test('generateUserDailyReport returns correct data structure', function () {
    $mockUserId = 1;
    $mockTransactions = [
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01', 'vanished_at' => null],
        ['id' => 2, 'amount' => 200, 'date' => '2024-01-01', 'vanished_at' => null],
        ['id' => 3, 'amount' => 300, 'date' => '2024-01-01', 'vanished_at' => '2024-01-01'], 
    ];

    $this->transactionService->shouldReceive('getAllTransactionsByUserId')
        ->with($mockUserId)
        ->once()
        ->andReturn($mockTransactions);

    $result = $this->reportingService->generateUserDailyReport($mockUserId);

    expect($result)->toBeArray()
        ->toHaveKeys(['totalAmount', 'numberOfTransactions', 'transactions', 'date', 'userId'])
        ->and($result['totalAmount'])->toBe(300)
        ->and($result['numberOfTransactions'])->toBe(2) 
        ->and($result['userId'])->toBe($mockUserId)
        ->and($result['date'])->toBe(date('Y-m-d'));
});

test('generateGlobalDailyReport returns cached data when available', function () {
    $mockCurrentDate = date('Y-m-d');
    $mockCacheKey = "global_daily_report_{$mockCurrentDate}";
    $mockCachedData = [
        'totalAmount' => 1000,
        'numberOfTransactions' => 5,
        'transactions' => [],
        'date' => $mockCurrentDate
    ];

    $this->redis->shouldReceive('get')
        ->with($mockCacheKey)
        ->once()
        ->andReturn(json_encode($mockCachedData));

    $this->transactionService->shouldNotReceive('getAllTransactions');

    $result = $this->reportingService->generateGlobalDailyReport();

    expect($result)->toBe($mockCachedData);
});

test('generateGlobalDailyReport generates new report when cache miss', function () {
    $mockCurrentDate = date('Y-m-d');
    $mockCacheKey = "global_daily_report_{$mockCurrentDate}";
    $mockTransactions = [
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01', 'vanished_at' => null],
        ['id' => 2, 'amount' => 200, 'date' => '2024-01-01', 'vanished_at' => null],
    ];

    $this->redis->shouldReceive('get')
        ->with($mockCacheKey)
        ->once()
        ->andReturn(false);

    $this->transactionService->shouldReceive('getAllTransactions')
        ->once()
        ->andReturn($mockTransactions);

    // Mock Redis set
    $this->redis->shouldReceive('setex')
        ->with($mockCacheKey, 300, Mockery::type('string'))
        ->once();

    $result = $this->reportingService->generateGlobalDailyReport();

    expect($result)->toBeArray()
        ->toHaveKeys(['totalAmount', 'numberOfTransactions', 'transactions', 'date'])
        ->and($result['totalAmount'])->toBe(300)
        ->and($result['numberOfTransactions'])->toBe(2)
        ->and($result['date'])->toBe($mockCurrentDate);
});

test('exportUserDailyReportToCSV creates file with correct content', function () {
    $mockUserId = 1;
    $mockTransactions = [
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01', 'vanished_at' => null],
        ['id' => 2, 'amount' => 200, 'date' => '2024-01-01', 'vanished_at' => null],
    ];

    // Mock transaction service
    $this->transactionService->shouldReceive('getAllTransactionsByUserId')
        ->with($mockUserId)
        ->once()
        ->andReturn($mockTransactions);

    $this->reportingService->generateUserDailyReport($mockUserId);

    $reportsDir = __DIR__ . '/../../src/reports/userReports';
    $csvFilePath = $reportsDir . '/user_daily_report_' . $mockUserId . '_' . date('Y-m-d') . '.csv';
    
    expect(file_exists($csvFilePath))->toBeTrue();
    
    if (file_exists($csvFilePath)) {
        unlink($csvFilePath);
    }
});

test('exportGlobalDailyReportToCSV creates file with correct content', function () {
    $mockTransactions = [
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01', 'vanished_at' => null],
        ['id' => 2, 'amount' => 200, 'date' => '2024-01-01', 'vanished_at' => null],
    ];

    // Mock Redis cache miss
    $this->redis->shouldReceive('get')
        ->andReturn(false);

    $this->transactionService->shouldReceive('getAllTransactions')
        ->once()
        ->andReturn($mockTransactions);

    // Mock Redis set
    $this->redis->shouldReceive('setex');

    $this->reportingService->generateGlobalDailyReport();

    $reportsDir = __DIR__ . '/../../src/reports/globalReports';
    $csvFilePath = $reportsDir . '/global_daily_report_' . date('Y-m-d') . '.csv';
    
    expect(file_exists($csvFilePath))->toBeTrue();
    
    if (file_exists($csvFilePath)) {
        unlink($csvFilePath);
    }
});
