<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Models\UserModel;

class TransactionService {
    private $transactionRepository;
    private $userRepository;
    public function __construct(TransactionRepository $transactionRepository, UserRepository $userRepository) {
        $this->transactionRepository = $transactionRepository;
        $this->userRepository = $userRepository;
    }

    public function createTransaction(TransactionModel $transaction): TransactionModel {
        $user = $this->userRepository->getUserById($transaction->getUserId());
    
        if (!$user) {
            throw new \Exception("User not found");
        }
    
        $newCredit = $user->getCredit() + $transaction->getAmount();
    
        if ($newCredit < 0) {
            error_log("User with id " . $user->getId() . " has insufficient balance $" . $user->getCredit() . ", transaction failed");
            throw new \Exception("User has insufficient balance, transaction failed");
        }
    
        return $this->transactionRepository->createTransaction($transaction, $newCredit);
    }

    public function softDeleteTransaction(int $id): bool {
        try{
            $transaction = $this->transactionRepository->getSingleTransactionById($id);

            return $this->transactionRepository->softDeleteTransaction($transaction->getId());
        } catch(\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Transaction of the provided id was not found');
        }

    }

    public function getAllTransactionsByUserId(int $userId): array {
        return $this->transactionRepository->getAllTransactionsByUserId($userId);
    }

    public function getAllTransactions(): array {
        return $this->transactionRepository->getAllTransactions();
    }

    private function checkSufficientBalance(UserModel $user, float $amount): bool {
        $user_balance = $user->getCredit();

        $new_balance = $user_balance + $amount;

        return $new_balance >= 0;
    }
}
