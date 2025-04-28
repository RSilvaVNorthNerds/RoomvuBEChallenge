<?php

namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;
use Faker\Factory as FakerFactory;

class UserService {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function createUser(UserModel $user): void {
        $this->userRepository->createUser($user);
    }
    
    public function populateFakeUsers(int $amount): void {
        $faker = FakerFactory::create();
        
        for ($i = 0; $i < $amount; $i++) {
            $user = new UserModel(
                name: $faker->name(),
                credit: $faker->randomFloat(2,0,1000),
            );

            $this->userRepository->createUser($user);
        }
    }

    public function getUserBalance(int $userId): float {
        return $this->userRepository->getUserBalance($userId);
    }

    public function getUserById(int $userId): ?UserModel {
        return $this->userRepository->getUserById($userId);
    }
}
