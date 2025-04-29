<?php

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\TransactionService;
use App\Models\UserModel;

beforeEach(function () {
    $this->transactionRepository = mock(TransactionRepository::class);
    $this->userRepository = mock(UserRepository::class);
    $this->transactionService = new TransactionService($this->transactionRepository, $this->userRepository);
});

afterEach(function () {
    Mockery::close();
});

/**
 * Tests successful transaction processing flow:
 * - Verifies user exists and has sufficient balance
 * - Creates transaction record
 * - Updates user's credit balance
 * - Returns transaction with correct ID
 */
test('runTransaction successfully processes a valid transaction', function () {
    $mockUserId = 1;
    $mockAmount = 100;
    $mockDate = (new DateTime())->format('Y-m-d');
    $mockTransactionId = '1';
    
    $mockUser = new UserModel('John Doe', 500, $mockUserId);
    $mockTransaction = new TransactionModel($mockUserId, $mockAmount, $mockDate);
    
    $this->userRepository->shouldReceive('getUserById')
        ->once()
        ->with($mockUserId)
        ->andReturn($mockUser);
        
    $this->transactionRepository->shouldReceive('createTransaction')
        ->once()
        ->with($mockTransaction)
        ->andReturn($mockTransactionId);
        
    $this->userRepository->shouldReceive('updateCredit')
        ->once()
        ->with($mockUser);
    
    $result = $this->transactionService->runTransaction($mockTransaction);
    
    expect($result->getId())->toBe((int) $mockTransactionId)
        ->and($result->getUserId())->toBe($mockUserId);
});

/**
 * Tests transaction failure when user has insufficient balance:
 * - Verifies exception is thrown when withdrawal amount exceeds available credit
 * - Ensures transaction is not processed when balance check fails
 */
test('runTransaction throws exception when user has insufficient balance', function () {
    $mockUserId = 1;
    $mockAmount = -600; // Negative amount for withdrawal
    $mockDate = (new DateTime())->format('Y-m-d H:i:s');
    
    $mockUser = new UserModel('John Doe', 500, $mockUserId); // User has 500 credit
    $mockTransaction = new TransactionModel($mockUserId, $mockAmount, $mockDate);
    
    $this->userRepository->shouldReceive('getUserById')
        ->once()
        ->with($mockUserId)
        ->andReturn($mockUser);
    
    expect(fn() => $this->transactionService->runTransaction($mockTransaction))
        ->toThrow(\Exception::class, 'User has insufficient balance, transaction failed');
});

/**
 * Tests successful soft deletion of a transaction:
 * - Verifies transaction exists before deletion
 * - Performs soft delete operation
 * - Confirms deletion was successful
 */
test('softDeleteTransaction successfully deletes an existing transaction', function () {
    $mockTransactionId = 1;
    $mockDate = (new DateTime())->format('Y-m-d H:i:s');
    $mockTransaction = new TransactionModel(1, 100, $mockDate, $mockTransactionId);
    
    $this->transactionRepository->shouldReceive('getSingleTransactionById')
        ->once()
        ->with($mockTransactionId)
        ->andReturn($mockTransaction);
        
    $this->transactionRepository->shouldReceive('softDeleteTransaction')
        ->once()
        ->with($mockTransactionId)
        ->andReturn(true);
    
    $result = $this->transactionService->softDeleteTransaction($mockTransactionId);
    
    expect($result)->toBeTrue();
});

/**
 * Tests error handling for non-existent transactions:
 * - Verifies exception is thrown when attempting to delete non-existent transaction
 * - Ensures proper error message is returned
 */
test('softDeleteTransaction throws exception when transaction not found', function () {
    $mockTransactionId = 999;
    
    $this->transactionRepository->shouldReceive('getSingleTransactionById')
        ->once()
        ->with($mockTransactionId)
        ->andThrow(new \Exception('Transaction not found'));
    
    expect(fn() => $this->transactionService->softDeleteTransaction($mockTransactionId))
        ->toThrow(\Exception::class, 'Transaction of the provided id was not found');
});

/**
 * Tests retrieval of user-specific transactions:
 * - Verifies all transactions for a specific user are returned
 * - Confirms correct number of transactions are retrieved
 * - Ensures transaction data matches expected format
 */
test('getAllTransactionsByUserId returns transactions for specific user', function () {
    $mockUserId = 1;
    $mockDate = (new DateTime())->format('Y-m-d H:i:s');
    $mockTransactions = [
        new TransactionModel($mockUserId, 100, $mockDate, 1),
        new TransactionModel($mockUserId, -50, $mockDate, 2)
    ];
    
    $this->transactionRepository->shouldReceive('getAllTransactionsByUserId')
        ->once()
        ->with($mockUserId)
        ->andReturn($mockTransactions);
    
    $result = $this->transactionService->getAllTransactionsByUserId($mockUserId);
    
    expect($result)->toBe($mockTransactions)
        ->and(count($result))->toBe(2);
});

/**
 * Tests retrieval of all transactions in the system:
 * - Verifies all transactions are returned regardless of user
 * - Confirms correct number of transactions are retrieved
 * - Ensures transaction data matches expected format
 */
test('getAllTransactions returns all transactions', function () {
    $mockDate = (new DateTime())->format('Y-m-d H:i:s');
    $mockTransactions = [
        new TransactionModel(1, 100, $mockDate, 1),
        new TransactionModel(2, 200, $mockDate, 2)
    ];
    
    $this->transactionRepository->shouldReceive('getAllTransactions')
        ->once()
        ->andReturn($mockTransactions);
    
    $result = $this->transactionService->getAllTransactions();
    
    expect($result)->toBe($mockTransactions)
        ->and(count($result))->toBe(2);
});
