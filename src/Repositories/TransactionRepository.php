<?php

namespace App\Repositories;

use App\Models\TransactionModel;
use PDO;
use PDOException;

class TransactionRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createTransaction(TransactionModel $transaction): TransactionModel {
        try {
            $query = $this->pdo->prepare("INSERT INTO transactions (user_id, amount, date, vanished_at) VALUES (:user_id, :amount, :date, :vanished_at)");
            $success = $query->execute([
                'user_id' => $transaction->getUserId(),
                'amount' => $transaction->getAmount(),
                'date' => $transaction->getDate(),
            ]);

            if (!$success) {
                error_log("Failed to create transaction");
                throw new PDOException("Failed to create transaction");
            }

            $transaction_id = $this->pdo->lastInsertId();

            return new TransactionModel(
                $transaction_id,
                $transaction->getUserId(),
                $transaction->getAmount(),
                $transaction->getDate(),
                $transaction->getVanishedAt()
            );
        } catch (PDOException $e) {
            error_log("PDOException in createTransaction: " . $e->getMessage());
            throw $e; // Re-throw to be handled by the service layer
        }
    }

    public function softDeleteTransaction(int $id): bool {
        $query = $this->pdo->prepare("UPDATE transactions SET vanished_at = CURRENT_TIMESTAMP WHERE id = :id");

        try {
            $success = $query->execute(['id' => $id]);

            if (!$success) {
                error_log("PDOException: Failed to soft delete transaction with id: " . $id);
                throw new PDOException("Failed to soft delete transaction with id: " . $id);
            }

            return $success;
        } catch (PDOException $e) {
            error_log("PDOException: " . $e->getMessage());

            return False;
        }
    }
}

