<?php

use App\Models\UserModel;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Mockery;
use Mockery\MockInterface;

beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepository::class);
    $this->userService = new UserService($this->userRepository);
});

afterEach(function () {
    Mockery::close();
});

test('createUser calls repository with correct user model', function () {
    $user = new UserModel(name: 'John Doe', credit: 100.00);
    
    $this->userRepository
        ->shouldReceive('createUser')
        ->once()
        ->with($user);
    
    $this->userService->createUser($user);
});

test('populateFakeUsers creates specified number of users', function () {
    $amount = 5;
    
    $this->userRepository
        ->shouldReceive('createUser')
        ->times($amount);
    
    $this->userService->populateFakeUsers($amount);
});

test('getUserBalance returns correct balance from repository', function () {
    $userId = 1;
    $expectedBalance = 500.00;
    
    $this->userRepository
        ->shouldReceive('getUserBalance')
        ->once()
        ->with($userId)
        ->andReturn($expectedBalance);
    
    $balance = $this->userService->getUserBalance($userId);
    
    expect($balance)->toBe($expectedBalance);
});

test('getUserById returns user model from repository', function () {
    $userId = 1;
    $expectedUser = new UserModel(name: 'John Doe', credit: 100.00);
    
    $this->userRepository
        ->shouldReceive('getUserById')
        ->once()
        ->with($userId)
        ->andReturn($expectedUser);
    
    $user = $this->userService->getUserById($userId);
    
    expect($user)->toBe($expectedUser);
});

test('getUserById returns null when user not found', function () {
    $userId = 999;
    
    $this->userRepository
        ->shouldReceive('getUserById')
        ->once()
        ->with($userId)
        ->andReturn(null);
    
    $user = $this->userService->getUserById($userId);
    
    expect($user)->toBeNull();
});
