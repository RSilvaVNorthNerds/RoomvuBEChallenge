<?php

use App\Models\UserModel;
use App\Repositories\UserRepository;
use App\Services\UserService;

beforeEach(function () {
    $this->userRepository = mock(UserRepository::class);
    $this->userService = new UserService($this->userRepository);
});

afterEach(function () {
    Mockery::close();
});

test('createUser calls repository with correct user model', function () {
    $mockUser = new UserModel(name: 'John Doe', credit: 100.00);
    
    $this->userRepository
        ->shouldReceive('createUser')
        ->once()
        ->with($mockUser);
    
    $this->userService->createUser($mockUser);
});

test('populateFakeUsers creates specified number of users', function () {
    $mockAmount = 5;
    
    $this->userRepository
        ->shouldReceive('createUser')
        ->times($mockAmount);
    
    $this->userService->populateFakeUsers($mockAmount);
});

test('getUserBalance returns correct balance from repository', function () {
    $mockUserId = 1;
    $mockExpectedBalance = 500.00;
    
    $this->userRepository
        ->shouldReceive('getUserBalance')
        ->once()
        ->with($mockUserId)
        ->andReturn($mockExpectedBalance);
    
    $balance = $this->userService->getUserBalance($mockUserId);
    
    expect($balance)->toBe($mockExpectedBalance);
});

test('getUserById returns user model from repository', function () {
    $mockUserId = 1;
    $mockExpectedUser = new UserModel(name: 'John Doe', credit: 100.00);
    
    $this->userRepository
        ->shouldReceive('getUserById')
        ->once()
        ->with($mockUserId)
        ->andReturn($mockExpectedUser);
    
    $user = $this->userService->getUserById($mockUserId);
    
    expect($user)->toBe($mockExpectedUser);
});

test('getUserById returns null when user not found', function () {
    $mockUserId = 999;
    
    $this->userRepository
        ->shouldReceive('getUserById')
        ->once()
        ->with($mockUserId)
        ->andReturn(null);
    
    $user = $this->userService->getUserById($mockUserId);
    
    expect($user)->toBeNull();
});
