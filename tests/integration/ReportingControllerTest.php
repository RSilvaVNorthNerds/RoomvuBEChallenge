<?php

use App\Controllers\ReportingController;
use App\Services\ReportingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->reportingService = mock(ReportingService::class);
    $this->controller = new ReportingController($this->reportingService);
});

/**
 * Tests that the controller properly validates and returns the correct 400 error
 * when no user_id is provided in the request. This ensures proper input validation
 * and error handling for missing required parameters.
 */
test('generateUserDailyReport returns 400 when user_id is missing', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode([]));
    
    $response = $this->controller->generateUserDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'User ID is required but was not provided'
    ]);
});

/**
 * Tests that the controller properly validates and returns the correct 400 error
 * when an invalid user_id is provided. This ensures the API rejects invalid
 * user identifiers and maintains data integrity.
 */
test('generateUserDailyReport returns 400 when user_id is invalid', function () {
    $mockRequest = new Request([], [], [], [], [], [], json_encode(['user_id' => 'invalid']));
    
    $response = $this->controller->generateUserDailyReport($mockRequest);
    
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'error' => 'User ID is required but was not provided'
    ]);
});

/**
 * Tests the successful generation of a user daily report. Verifies that:
 * 1. The controller properly processes valid input
 * 2. The service is called with correct parameters
 * 3. The response contains the expected report data
 * 4. The HTTP status code is 200
 */
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

/**
 * Tests error handling when the reporting service throws an exception.
 * Verifies that the controller properly catches and formats service errors
 * into appropriate HTTP 500 responses with meaningful error messages.
 */
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

/**
 * Tests the successful generation of a global daily report. Verifies that:
 * 1. The controller properly handles requests without parameters
 * 2. The service is called correctly
 * 3. The response contains the expected report data
 * 4. The HTTP status code is 200
 */
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

/**
 * Tests error handling for global report generation when the service fails.
 * Ensures that service-level errors are properly caught and converted into
 * appropriate HTTP 500 responses with descriptive error messages.
 */
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
