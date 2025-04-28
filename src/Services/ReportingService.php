<?php

namespace App\Services;

class ReportingService {
    private $transactionService;

    public function __construct() {
        $this->transactionService = new TransactionService();
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
        
        $csvFile = fopen($csvFilePath, 'w');

        if ($csvFile === false) {
            throw new \Exception("Failed to open file for writing. Check path and permissions.");
        }

        fputcsv($csvFile, ['Date', 'Amount'], ",", "\"", "\\");
        foreach ($activeTransactions as $transaction) {
            fputcsv($csvFile, [$transaction['date'], $transaction['amount']], ",", "\"", "\\");
        }
        // Add summary row
        fputcsv($csvFile, ['Total', $totalAmount, "Transactions: $numberOfTransactions"], ",", "\"", "\\");
        fclose($csvFile);
    }

    public function generateGlobalDailyReport() {
        $allTransactions = $this->transactionService->getAllTransactions();

        $activeTransactions = array_filter($allTransactions, function($transaction) {
            return $transaction['vanished_at'] === null;
        });

        $numberOfTransactions = count($activeTransactions);
        $totalAmount = array_sum(array_column($activeTransactions, 'amount'));

        $currentDate = date('Y-m-d');
        $reportsDir = __DIR__ . '/../reports/globalReports';

        $csvFilePath = $reportsDir . '/global_daily_report_' . $currentDate . '.csv';

        $csvFile = fopen($csvFilePath, 'w');

        if ($csvFile === false) {
            throw new \Exception("Failed to open file for writing. Check path and permissions.");
        }

        fputcsv($csvFile, ['Date', 'Amount'], ",", "\"", "\\");
        
        foreach ($activeTransactions as $transaction) {
            fputcsv($csvFile, [$transaction['date'], $transaction['amount']], ",", "\"", "\\");
        }

        fputcsv($csvFile, ['Total', $totalAmount, "Transactions: $numberOfTransactions"], ",", "\"", "\\");
        fclose($csvFile);
    }
}