<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Models\TransactionModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController {
    private $transactionService;

    public function __construct() {
        $this->transactionService = new TransactionService();
    }

    public function createTransaction(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        // Get and sanitize input data
        $user_id = (int) filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);
        $amount = (float) filter_var($data['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $date = filter_var($data['date'], FILTER_SANITIZE_SPECIAL_CHARS);

        // Validate required fields
        if (empty($user_id) || empty($amount) || empty($date)) {
            return new JsonResponse([
                'error' => 'Missing required fields: user_id, amount, and date are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate data types
        if (!is_numeric($user_id) || !is_numeric($amount)) {
            return new JsonResponse([
                'error' => 'Invalid data types: user_id and amount must be numeric'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate date format (assuming YYYY-MM-DD format)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return new JsonResponse([
                'error' => 'Invalid date format. Please use YYYY-MM-DD format'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Create transaction with validated and sanitized data
        $transaction = new TransactionModel($user_id, $amount, $date);

        try {
            $createdUser = $this->transactionService->runTransaction($transaction);
            return new JsonResponse([
                'user_id' => $createdUser->getUserId(),
                'amount' => $createdUser->getAmount(),
                'date' => $createdUser->getDate(),
                'vanished_at' => $createdUser->getVanishedAt()
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create transaction: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function archiveTransaction(Request $request, Response $response): Response {
        $id = filter_var($request->request->get('id'), FILTER_SANITIZE_NUMBER_INT);

        if (empty($id)) {
            return new JsonResponse([
                'error' => 'Transaction ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->transactionService->softDeleteTransaction($id);
            return new JsonResponse(['message' => 'Transaction archived']);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to archive transaction: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
