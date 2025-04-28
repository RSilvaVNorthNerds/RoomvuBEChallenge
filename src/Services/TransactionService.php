<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;

class TransactionService {
    private $transactionRepository;
    private $userRepository;
    public function __construct() {
        $this->transactionRepository = new TransactionRepository();
        $this->userRepository = new UserRepository();
    }

    public function runTransaction(TransactionModel $transaction): TransactionModel {
        $user = $this->userRepository->getUserById($transaction->getUserId());
        $amount = $transaction->getAmount();

        //check if user has enough balance to make the transaction if amount is negative
        if($amount < 0) {
            $user_balance = $user->getCredit();

            $new_balance = $user_balance + $amount;

            if($new_balance < 0) {
                throw new \Exception('User has insufficient balance, transaction failed');
            }
        }
        
        $transaction_id = $this->transactionRepository->createTransaction($transaction);

        $user->setCredit($user->getCredit() + $transaction->getAmount());

        $this->userRepository->updateCredit($user);

        return new TransactionModel(
            $transaction->getUserId(),
            $transaction->getAmount(),
            $transaction->getDate(),
            $transaction_id,
            $transaction->getVanishedAt()
        );
    }

    public function softDeleteTransaction(int $id): bool {
        try{
            $transaction = $this->transactionRepository->getTransactionById($id);

            return $this->transactionRepository->softDeleteTransaction($transaction->getId());
        } catch(\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Transaction of the provided id was not found');
        }

    }
}
