<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;

class TransactionService {
    private $transactionRepository;

    public function __construct() {
        $this->transactionRepository = new TransactionRepository();
    }

    public function runTransaction(TransactionModel $transaction): TransactionModel {
        return $this->transactionRepository->createTransaction($transaction);
    }

    public function softDeleteTransaction(int $id): bool {
        return $this->transactionRepository->softDeleteTransaction($id);
    }
}
