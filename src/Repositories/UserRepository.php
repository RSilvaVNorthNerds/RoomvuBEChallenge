<?php

namespace App\Repositories;

use App\Models\UserModel;
use App\Config\Database;

class UserRepository {
    private $pdo;

    public function __construct(Database $database) {
        $this->pdo = $database->getConnection();
    }

    public function createUser(UserModel $user): string {
        $query = $this->pdo->prepare("INSERT INTO users (name, credit) VALUES (:name, :credit)");
        $query->execute([
            'name' => $user->getName(),
            'credit' => $user->getCredit()
        ]);

        return $this->pdo->lastInsertId();
    }

    public function getUserById(int $id): ?UserModel {
        $query = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $query->execute([
            'id' => $id
        ]);

        $user = $query->fetch($this->pdo::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        return new UserModel( $user['name'], $user['credit'], $user['id']);
    }
    
    public function updateCredit(UserModel $user): void {
        $query = $this->pdo->prepare("UPDATE users SET credit = :credit WHERE id = :id");
        $query->execute([
            'credit' => $user->getCredit(),
            'id' => $user->getId()
        ]);
    }

    public function getUserBalance(int $userId): float {
        $query = $this->pdo->prepare("SELECT credit FROM users WHERE id = :id");
        $query->execute([
            'id' => $userId
        ]);

        $user = $query->fetch($this->pdo::FETCH_ASSOC);

        return $user['credit'];
    }
}
