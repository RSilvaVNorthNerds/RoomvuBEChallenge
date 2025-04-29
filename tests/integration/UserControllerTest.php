<?php

use App\Controllers\UserController;
use App\Models\UserModel;
use App\Services\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->userService = Mockery::mock(UserService::class);
    $this->controller = new UserController($this->userService);
});

afterEach(function () {
    Mockery::close();
});

/**
 * Tests successful user creation with valid input data
 * Verifies that the controller properly handles valid user data and returns appropriate response
 * Ensures the service is called correctly and response contains expected user data
 */
test('create user successfully', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'name' => 'John Doe',
        'credit' => 100.50
    ]));

    $this->userService->shouldReceive('createUser')
        ->once()
        ->with(Mockery::type(UserModel::class));

    $response = $this->controller->createUser($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))
        ->toHaveKeys(['name', 'credit'])
        ->name->toBe('John Doe')
        ->credit->toBe(100.5);
});

/**
 * Tests user creation validation with missing required fields
 * Verifies that the controller properly handles invalid input by returning appropriate error response
 * Ensures validation prevents creation of users without required name and credit fields
 */
test('create user fails with missing required fields', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'name' => ''
    ]));

    $response = $this->controller->createUser($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))
        ->toHaveKey('error')
        ->error->toBe('Missing required fields: name and credit are required');
});

/**
 * Tests successful population of fake users with valid amount
 * Verifies that the controller properly handles the fake user population request
 * Ensures the service is called with correct amount and returns success response
 */
test('populate fake users successfully', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([
        'amount' => 5
    ]));

    $this->userService->shouldReceive('populateFakeUsers')
        ->once()
        ->with(5);

    $response = $this->controller->populateFakeUsers($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))
        ->toHaveKey('message')
        ->message->toBe('Users populated');
});

/**
 * Tests fake user population validation with missing amount
 * Verifies that the controller properly handles missing amount parameter
 * Ensures validation prevents population without specifying number of users to create
 */
test('populate fake users fails with missing amount', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([]));

    $response = $this->controller->populateFakeUsers($mockRequest);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))
        ->toHaveKey('error')
        ->error->toBe('Amount is required');
});
