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
    $mockRequest = new Request([], [], [], [], [], [], json_encode([]));
    
    $response = $this->controller->generateUserDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'User ID is required but was not provided'
    ]);
});

test('generateUserDailyReport returns 400 when user_id is invalid', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode(['user_id' => 'invalid']));
    
    $response = $this->controller->generateUserDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'User ID is required but was not provided'
    ]);
});

test('generateUserDailyReport returns 200 with report data when successful', function () {
    $mockUserId = 1;
    $mockReportData = ['transactions' => [], 'summary' => []];
    
    $this->reportingService
        ->shouldReceive('generateUserDailyReport')
        ->with($mockUserId)
        ->once()
        ->andReturn($mockReportData);
    
    $mockRequest = new Request([], [], [], [], [], [], json_encode(['user_id' => $mockUserId]));
    
    $response = $this->controller->generateUserDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'message' => 'User daily report generated successfully',
        'reportData' => $mockReportData
    ]);
});

test('generateUserDailyReport returns 500 when service throws an exception', function () {
    $mockUserId = 1;
    
    $this->reportingService
        ->shouldReceive('generateUserDailyReport')
        ->with($mockUserId)
        ->once()
        ->andThrow(new \Exception('Service error'));
    
    $mockRequest = new Request([], [], [], [], [], [], json_encode(['user_id' => $mockUserId]));
    
    $response = $this->controller->generateUserDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'Failed to get user daily report: Service error'
    ]);
});

test('generateGlobalDailyReport returns 200 with report data when successful', function () {
    $mockReportData = ['transactions' => [], 'summary' => []];
    
    $this->reportingService
        ->shouldReceive('generateGlobalDailyReport')
        ->once()
        ->andReturn($mockReportData);
    
    $mockRequest = new Request();
    
    $response = $this->controller->generateGlobalDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'message' => 'Global daily report generated successfully',
        'reportData' => $mockReportData
    ]);
});

test('generateGlobalDailyReport returns 500 when service throws an exception', function () {
    $this->reportingService
        ->shouldReceive('generateGlobalDailyReport')
        ->once()
        ->andThrow(new \Exception('Service error'));
    
    $mockRequest = new Request();
    
    $response = $this->controller->generateGlobalDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'Failed to get global daily report: Service error'
    ]);
});
