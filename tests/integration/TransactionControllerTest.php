<?php

use App\Controllers\TransactionController;
use App\Services\TransactionService;
use App\Services\UserService;
use App\Models\TransactionModel;
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

test('create transaction successfully', function () {
    $request = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 1,
        'amount' => 100.50,
        'date' => '2024-03-20'
    ]));

    $user = new \stdClass();
    $user->id = 1;

    $transaction = new TransactionModel(1, 100.50, '2024-03-20');

    $this->userService->shouldReceive('getUserById')
        ->with(1)
        ->once()
        ->andReturn($user);

    $this->transactionService->shouldReceive('runTransaction')
        ->with(Mockery::type(TransactionModel::class))
        ->once()
        ->andReturn($transaction);

    $response = $this->controller->createTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'user_id' => 1,
        'amount' => 100.50,
        'date' => '2024-03-20',
        'vanished_at' => null
    ]);
});

test('create transaction fails with missing required fields', function () {
    $request = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 1,
        // Missing amount and date
    ]));

    $response = $this->controller->createTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

test('create transaction fails with invalid date format', function () {
    $request = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 1,
        'amount' => 100.50,
        'date' => 'invalid-date'
    ]));

    $response = $this->controller->createTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

test('create transaction fails when user does not exist', function () {
    $request = new Request([], [], [], [], [], [], json_encode([
        'user_id' => 999,
        'amount' => 100.50,
        'date' => '2024-03-20'
    ]));

    $this->userService->shouldReceive('getUserById')
        ->with(999)
        ->once()
        ->andReturn(null);

    $response = $this->controller->createTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

test('archive transaction successfully', function () {
    $request = new Request([], [], [], [], [], [], json_encode([
        'id' => 1
    ]));

    $this->transactionService->shouldReceive('softDeleteTransaction')
        ->with(1)
        ->once();

    $response = $this->controller->archiveTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'message' => 'Transaction archived'
    ]);
});

test('archive transaction fails with missing id', function () {
    $request = new Request([], [], [], [], [], [], json_encode([]));

    $response = $this->controller->archiveTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});

test('archive transaction fails with invalid id', function () {
    $request = new Request([], [], [], [], [], [], json_encode([
        'id' => 'invalid'
    ]));

    $response = $this->controller->archiveTransaction($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toHaveKey('error');
});
