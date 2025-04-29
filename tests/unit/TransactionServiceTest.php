<?php

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\TransactionService;
use App\Models\UserModel;
use PHPUnit\Framework\MockObject\MockObject;

beforeEach(function () {
    $this->transactionRepository = $this->createMock(TransactionRepository::class);
    $this->userRepository = $this->createMock(UserRepository::class);
    $this->transactionService = new TransactionService($this->transactionRepository, $this->userRepository);
});

test('runTransaction successfully processes a valid transaction', function () {
    $userId = 1;
    $amount = 100;
    $date = (new DateTime())->format('Y-m-d');
    $transactionId = '1';
    
    $user = new UserModel( 'John Doe', 500, $userId);
    $transaction = new TransactionModel($userId, $amount, $date);
    
    $this->userRepository->expects($this->once())
        ->method('getUserById')
        ->with($userId)
        ->willReturn($user);
        
    $this->transactionRepository->expects($this->once())
        ->method('createTransaction')
        ->with($transaction)
        ->willReturn($transactionId);
        
    $this->userRepository->expects($this->once())
        ->method('updateCredit')
        ->with($user);
    
    $result = $this->transactionService->runTransaction($transaction);
    
    expect($result->getId())->toBe((int) $transactionId)
        ->and($result->getUserId())->toBe($userId);
});

test('runTransaction throws exception when user has insufficient balance', function () {
    $userId = 1;
    $amount = -600; // Negative amount for withdrawal
    $date = (new DateTime())->format('Y-m-d H:i:s');
    
    $user = new UserModel( 'John Doe', 500, $userId); // User has 500 credit
    $transaction = new TransactionModel($userId, $amount, $date);
    
    $this->userRepository->expects($this->once())
        ->method('getUserById')
        ->with($userId)
        ->willReturn($user);
    
    expect(fn() => $this->transactionService->runTransaction($transaction))
        ->toThrow(\Exception::class, 'User has insufficient balance, transaction failed');
});

test('softDeleteTransaction successfully deletes an existing transaction', function () {
    $transactionId = 1;
    $date = (new DateTime())->format('Y-m-d H:i:s');
    $transaction = new TransactionModel(1, 100, $date, $transactionId);
    
    $this->transactionRepository->expects($this->once())
        ->method('getSingleTransactionById')
        ->with($transactionId)
        ->willReturn($transaction);
        
    $this->transactionRepository->expects($this->once())
        ->method('softDeleteTransaction')
        ->with($transactionId)
        ->willReturn(true);
    
    $result = $this->transactionService->softDeleteTransaction($transactionId);
    
    expect($result)->toBeTrue();
});

test('softDeleteTransaction throws exception when transaction not found', function () {
    $transactionId = 999;
    
    $this->transactionRepository->expects($this->once())
        ->method('getSingleTransactionById')
        ->with($transactionId)
        ->willThrowException(new \Exception('Transaction not found'));
    
    expect(fn() => $this->transactionService->softDeleteTransaction($transactionId))
        ->toThrow(\Exception::class, 'Transaction of the provided id was not found');
});

test('getAllTransactionsByUserId returns transactions for specific user', function () {
    $userId = 1;
    $date = (new DateTime())->format('Y-m-d H:i:s');
    $transactions = [
        new TransactionModel($userId, 100, $date, 1),
        new TransactionModel($userId, -50, $date, 2)
    ];
    
    $this->transactionRepository->expects($this->once())
        ->method('getAllTransactionsByUserId')
        ->with($userId)
        ->willReturn($transactions);
    
    $result = $this->transactionService->getAllTransactionsByUserId($userId);
    
    expect($result)->toBe($transactions)
        ->and(count($result))->toBe(2);
});

test('getAllTransactions returns all transactions', function () {
    $date = (new DateTime())->format('Y-m-d H:i:s');
    $transactions = [
        new TransactionModel(1, 100, $date, 1),
        new TransactionModel(2, 200, $date, 2)
    ];
    
    $this->transactionRepository->expects($this->once())
        ->method('getAllTransactions')
        ->willReturn($transactions);
    
    $result = $this->transactionService->getAllTransactions();
    
    expect($result)->toBe($transactions)
        ->and(count($result))->toBe(2);
});
