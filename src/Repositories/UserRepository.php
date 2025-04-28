<?php

namespace App\Repositories;

use App\Models\UserModel;
use PDO;

class UserRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createUser(UserModel $user): void {
        $query = $this->pdo->prepare("INSERT INTO users (name, credit) VALUES (:name, :credit)");
        $query->execute([
            'name' => $user->getName(),
            'credit' => $user->getCredit()
        ]);
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

        return new UserModel($user['id'], $user['name'], $user['credit']);
    }
    
    public function updateCredit(UserModel $user): void {
        $query = $this->pdo->prepare("UPDATE users SET credit = :credit WHERE id = :id");
        $query->execute([
            'credit' => $user->getCredit(),
            'id' => $user->getId()
        ]);
    }
    
}
