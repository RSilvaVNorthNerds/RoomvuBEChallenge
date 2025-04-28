<?php

namespace App\Services;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ReportingService {
    private $transactionService;
    private $redis;

    public function __construct() {
        $this->transactionService = new TransactionService();

        $redisConnection = RedisAdapter::createConnection(
            "redis://{$_ENV['REDIS_HOST']}:{$_ENV['REDIS_PORT']}"
        );
        $this->redis = $redisConnection;
    }

    public function generateUserDailyReport($userId) {
        $userTransactions = $this->transactionService->getAllTransactionsByUserId($userId);

        // filter out the transactions that have been archived
        $activeTransactions = array_filter($userTransactions, function($transaction) {
            return $transaction['vanished_at'] === null;
        });

        $numberOfTransactions = count($activeTransactions);
        $totalAmount = array_sum(array_column($activeTransactions, 'amount'));

        // generate a csv file with the transactions
        $currentDate = date('Y-m-d');
        
        // Create absolute path using __DIR__
        $reportsDir = __DIR__ . '/../reports/userReports';
        
        // Create directory if it doesn't exist
        if (!file_exists($reportsDir)) {
            mkdir($reportsDir, 0777, true);
        }
        
        $csvFilePath = $reportsDir . '/user_daily_report_' . $userId . '_' . $currentDate . '.csv';

        $this->exportUserDailyReportToCSV($activeTransactions, $csvFilePath, $totalAmount, $numberOfTransactions);

        return [
            'totalAmount' => $totalAmount,
            'numberOfTransactions' => $numberOfTransactions,
            'transactions' => $activeTransactions,
            'date' => $currentDate,
            'userId' => $userId
        ];
    }

    public function generateGlobalDailyReport():array {
        $currentDate = date('Y-m-d');
        $cacheKey = "global_daily_report_{$currentDate}";
        
        // Try to get data from cache first
        $cachedData = $this->redis->get($cacheKey);
        if ($cachedData !== false) {
            error_log("Cache hit for global daily report");
            return json_decode($cachedData, true);
        }

        $allTransactions = $this->transactionService->getAllTransactions();

        $activeTransactions = array_filter($allTransactions, function($transaction) {
            return $transaction['vanished_at'] === null;
        });

        $numberOfTransactions = count($activeTransactions);
        $totalAmount = array_sum(array_column($activeTransactions, 'amount'));

        $reportsDir = __DIR__ . '/../reports/globalReports';
        $csvFilePath = $reportsDir . '/global_daily_report_' . $currentDate . '.csv';

        $this->exportGlobalDailyReportToCSV($activeTransactions, $csvFilePath, $totalAmount, $numberOfTransactions);

        $reportData = [
            'totalAmount' => $totalAmount,
            'numberOfTransactions' => $numberOfTransactions,
            'transactions' => $activeTransactions,
            'date' => $currentDate
        ];

        // Cache the report data for 5 minutes (300 seconds)
        $this->redis->setex($cacheKey, 300, json_encode($reportData));

        return $reportData;
    }

    private function exportGlobalDailyReportToCSV($transactions, $filePath, $totalAmount, $numberOfTransactions) {
        $csvFile = fopen($filePath, 'w');

        if ($csvFile === false) {
            throw new \Exception("Failed to open file for writing. Check path and permissions.");
        }

        fputcsv($csvFile, ['Date', 'Amount'], ",", "\"", "\\");
        
        foreach ($transactions as $transaction) {
            fputcsv($csvFile, [$transaction['date'], $transaction['amount']], ",", "\"", "\\");
        }

        fputcsv($csvFile, ['Total', $totalAmount, "Transactions: $numberOfTransactions"], ",", "\"", "\\");
        fclose($csvFile);
    }

    private function exportUserDailyReportToCSV($transactions, $csvFilePath, $totalAmount, $numberOfTransactions) {
        $csvFile = fopen($csvFilePath, 'w');

        if ($csvFile === false) {
            throw new \Exception("Failed to open file for writing. Check path and permissions.");
        }

        fputcsv($csvFile, ['Date', 'Amount'], ",", "\"", "\\");
        foreach ($transactions as $transaction) {
            fputcsv($csvFile, [$transaction['date'], $transaction['amount']], ",", "\"", "\\");
        }
        // Add summary row
        fputcsv($csvFile, ['Total', $totalAmount, "Transactions: $numberOfTransactions"], ",", "\"", "\\");
        fclose($csvFile);
    }
}