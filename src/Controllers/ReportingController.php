<?php

namespace App\Controllers;  

use App\Services\TransactionService;
use App\Services\ReportingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReportingController {
    private $reportingService;

    public function __construct(ReportingService $reportingService) {
        $this->reportingService = $reportingService;
    }

    public function generateUserDailyReport(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        $userId = (int) filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);

        if (empty($userId)) {
            return new JsonResponse([
                'error' => 'User ID is required but was not provided'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $reportData = $this->reportingService->generateUserDailyReport($userId);
            return new JsonResponse([
                'message' => 'User daily report generated successfully',
                'reportData' => $reportData
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to get user daily report: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generateGlobalDailyReport(Request $request): Response {
        try {
            $reportData = $this->reportingService->generateGlobalDailyReport();
            return new JsonResponse([
                'message' => 'Global daily report generated successfully',
                'reportData' => $reportData
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to get global daily report: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
