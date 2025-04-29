<?php

use App\Controllers\ReportingController;
use App\Services\ReportingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->reportingService = mock(ReportingService::class);
    $this->controller = new ReportingController($this->reportingService);
});

test('generateUserDailyReport returns 400 when user_id is missing', function () {
    $request = new Request([], [], [], [], [], [], json_encode([]));
    
    $response = $this->controller->generateUserDailyReport($request);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'User ID is required but was not provided'
    ]);
});

test('generateUserDailyReport returns 400 when user_id is invalid', function () {
    $request = new Request([], [], [], [], [], [], json_encode(['user_id' => 'invalid']));
    
    $response = $this->controller->generateUserDailyReport($request);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'User ID is required but was not provided'
    ]);
});

test('generateUserDailyReport returns 200 with report data when successful', function () {
    $userId = 1;
    $reportData = ['transactions' => [], 'summary' => []];
    
    $this->reportingService
        ->shouldReceive('generateUserDailyReport')
        ->with($userId)
        ->once()
        ->andReturn($reportData);
    
    $request = new Request([], [], [], [], [], [], json_encode(['user_id' => $userId]));
    
    $response = $this->controller->generateUserDailyReport($request);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'message' => 'User daily report generated successfully',
        'reportData' => $reportData
    ]);
});

test('generateUserDailyReport returns 500 when service throws an exception', function () {
    $userId = 1;
    
    $this->reportingService
        ->shouldReceive('generateUserDailyReport')
        ->with($userId)
        ->once()
        ->andThrow(new \Exception('Service error'));
    
    $request = new Request([], [], [], [], [], [], json_encode(['user_id' => $userId]));
    
    $response = $this->controller->generateUserDailyReport($request);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'Failed to get user daily report: Service error'
    ]);
});

test('generateGlobalDailyReport returns 200 with report data when successful', function () {
    $reportData = ['transactions' => [], 'summary' => []];
    
    $this->reportingService
        ->shouldReceive('generateGlobalDailyReport')
        ->once()
        ->andReturn($reportData);
    
    $request = new Request();
    
    $response = $this->controller->generateGlobalDailyReport($request);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'message' => 'Global daily report generated successfully',
        'reportData' => $reportData
    ]);
});

test('generateGlobalDailyReport returns 500 when service throws an exception', function () {
    $this->reportingService
        ->shouldReceive('generateGlobalDailyReport')
        ->once()
        ->andThrow(new \Exception('Service error'));
    
    $request = new Request();
    
    $response = $this->controller->generateGlobalDailyReport($request);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'Failed to get global daily report: Service error'
    ]);
});
