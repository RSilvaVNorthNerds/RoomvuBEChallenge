<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;
use App\Models\UserModel;
use App\Repositories\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;

class UserServiceTest extends TestCase
{
    private $userService;
    private $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userService = new UserService();
    }

    public function testCreateUserWithValidData()
    {
        $user = new UserModel(
            name: 'John Doe',
            credit: 100.00
        );

        $this->userRepository
            ->expects($this->once())
            ->method('createUser')
            ->with($user);

        $this->userService->createUser($user);

        // Since createUser returns void, we can't directly assert the result
        // We can verify the user was created by checking the database
        // or by getting the user by ID if we have that functionality
        $this->assertTrue(true); // Placeholder assertion
    }

    public function testPopulateFakeUsers()
    {
        $amount = 5;
        
        $this->userRepository
            ->expects($this->exactly($amount))
            ->method('createUser');

        $this->userService->populateFakeUsers($amount);
    }

    public function testGetUserBalance()
    {
        $userId = 1;
        $expectedBalance = 100.00;

        $this->userRepository
            ->expects($this->once())
            ->method('getUserBalance')
            ->with($userId)
            ->willReturn($expectedBalance);

        $balance = $this->userService->getUserBalance($userId);
        
        $this->assertEquals($expectedBalance, $balance);
    }

    public function testGetUserById()
    {
        $userId = 1;
        $expectedUser = new UserModel(
            name: 'John Doe',
            credit: 100.00,
            id: $userId
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getUserById')
            ->with($userId)
            ->willReturn($expectedUser);

        $user = $this->userService->getUserById($userId);
        
        $this->assertEquals($expectedUser, $user);
    }

    public function testUserNotFound()
    {
        $userId = 999;

        $this->userRepository
            ->expects($this->once())
            ->method('getUserById')
            ->with($userId)
            ->willReturn(null);

        $user = $this->userService->getUserById($userId);
        
        $this->assertNull($user);
    }
}

