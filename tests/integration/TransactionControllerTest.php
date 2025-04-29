<?php

use App\Controllers\TransactionController;
use App\Services\TransactionService;
use App\Services\UserService;
use App\Models\TransactionModel;
use App\Models\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->transactionService = Mockery::mock(TransactionService::class);
    $this->userService = Mockery::mock(UserService::class);
    $this->controller = new TransactionController($this->transactionService, $this->userService);
});

afterEach(function () {
    Mockery::close();
});

/**
 * Tests successful transaction creation with valid data
 * Verifies that a transaction can be created when all required fields are present and valid,
 * and that the response contains the correct transaction details
 */
test('create transaction successfully', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 1,
        'amount' => 100.50,
        'date' => '2024-03-20'
    ]));

    $mockUser = new UserModel('John Doe', 500.00, 1);

    $mockTransaction = new TransactionModel(1, 100.50, '2024-03-20');

    $this->userService->shouldReceive('getUserById')
        ->with(1)
        ->once()
        ->andReturn($mockUser);

    $this->transactionService->shouldReceive('runTransaction')
        ->with(Mockery::type(TransactionModel::class))
        ->once()
        ->andReturn($mockTransaction);

    $response = $this->controller->createTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'user_id' => 1,
        'amount' => 100.50,
        'date' => '2024-03-20',
        'vanished_at' => null
    ]);
});

/**
 * Tests transaction creation failure when required fields are missing
 * Verifies that the API returns a proper error response when essential data is not provided
 */
test('create transaction fails with missing required fields', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 1,
        // Missing amount and date
    ]));

    $response = $this->controller->createTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

/**
 * Tests transaction creation failure with invalid date format
 * Verifies that the API properly validates date format and returns an error for invalid dates
 */
test('create transaction fails with invalid date format', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 1,
        'amount' => 100.50,
        'date' => 'invalid-date'
    ]));

    $response = $this->controller->createTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

/**
 * Tests transaction creation failure for non-existent user
 * Verifies that the API prevents transactions from being created for users that don't exist
 */
test('create transaction fails when user does not exist', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 999,
        'amount' => 100.50,
        'date' => '2024-03-20'
    ]));

    $this->userService->shouldReceive('getUserById')
        ->with(999)
        ->once()
        ->andReturn(null);

    $response = $this->controller->createTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

/**
 * Tests successful transaction archiving
 * Verifies that a transaction can be properly archived (soft deleted) when a valid ID is provided
 */
test('archive transaction successfully', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'id' => 1
    ]));

    $this->transactionService->shouldReceive('softDeleteTransaction')
        ->with(1)
        ->once();

    $response = $this->controller->archiveTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'message' => 'Transaction archived'
    ]);
});

/**
 * Tests transaction archiving failure when ID is missing
 * Verifies that the API returns an error when attempting to archive without providing an ID
 */
test('archive transaction fails with missing id', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([]));

    $response = $this->controller->archiveTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

/**
 * Tests transaction archiving failure with invalid ID format
 * Verifies that the API properly validates the ID format and returns an error for invalid IDs
 */
test('archive transaction fails with invalid id', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'id' => 'invalid'
    ]));

    $response = $this->controller->archiveTransaction($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});
