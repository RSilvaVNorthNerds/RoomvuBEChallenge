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
        $transaction_id = $this->transactionRepository->createTransaction($transaction);

        return new TransactionModel(
            $transaction->getUserId(),
            $transaction->getAmount(),
            $transaction->getDate(),
            $transaction_id,
            $transaction->getVanishedAt()
        );
    }

    public function softDeleteTransaction(int $id): bool {
        return $this->transactionRepository->softDeleteTransaction($id);
    }
}
