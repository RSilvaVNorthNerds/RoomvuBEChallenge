<?php

namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;
use Faker\Factory as FakerFactory;

class UserService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function createUser(UserModel $user): void {
        $this->userRepository->createUser($user);
    }
    
    public function populateFakeUsers(int $amount): void {
        $faker = FakerFactory::create();
        $users = [];
        
        for ($i = 0; $i < $amount; $i++) {
            $user = new UserModel(
                name: $faker->name(),
                credit: $faker->randomFloat(2,0,1000),
            );

            $users[] = $this->createUser($user);
        }

        return $users;
    }
}
