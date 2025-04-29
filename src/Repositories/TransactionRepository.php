<?php

namespace App\Repositories;

use App\Models\TransactionModel;
use PDO;
use PDOException;
use App\Config\Database;

class TransactionRepository {
    private $pdo;

    public function __construct(Database $database) {
        $database->createTables();
        $this->pdo = $database->getConnection();
    }

    public function createTransaction(TransactionModel $transaction, float $newCredit): TransactionModel {
        try {
            $this->pdo->beginTransaction();
    
            $this->pdo->prepare("UPDATE users SET credit = :credit WHERE id = :id")
                ->execute([
                    'credit' => $newCredit,
                    'id' => $transaction->getUserId(),
                ]);
    
            $query = $this->pdo->prepare("
                INSERT INTO transactions (user_id, amount, date, vanished_at)
                VALUES (:user_id, :amount, :date, :vanished_at)
            ");
            $query->execute([
                'user_id' => $transaction->getUserId(),
                'amount' => $transaction->getAmount(),
                'date' => $transaction->getDate(),
                'vanished_at' => $transaction->getVanishedAt(),
            ]);
    
            $transactionId = $this->pdo->lastInsertId();
            $this->pdo->commit();
    
            return new TransactionModel(
                $transaction->getUserId(),
                $transaction->getAmount(),
                $transaction->getDate(),
                $transactionId,
                $transaction->getVanishedAt() ?? null
            );
    
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new PDOException("Failed to create transaction: " . $e->getMessage());
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
            return false;
        }
    }

    public function getSingleTransactionById(int $id): TransactionModel {
        $query = $this->pdo->prepare("SELECT * FROM transactions WHERE id = :id");
        $query->execute(['id' => $id]);
        
        $transaction = $query->fetch(PDO::FETCH_ASSOC);

        if(!$transaction) {
            throw new \Exception('Transaction of the provided id was not found');
        }

        return new TransactionModel($transaction['user_id'], $transaction['amount'], $transaction['date'], $transaction['id'], $transaction['vanished_at']);
    }

    public function getAllTransactionsByUserId(int $userId): array {
        $query = $this->pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id");
        $query->execute(['user_id' => $userId]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTransactions(): array {
        $query = $this->pdo->prepare("SELECT * FROM transactions");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

