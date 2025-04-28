<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Models\TransactionModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController {
    private $transactionService;

    public function __construct(TransactionService $transactionService) {
        $this->transactionService = $transactionService;
    }

    public function createTransaction(Request $request, Response $response): Response {
        $transaction = new TransactionModel(null, $request->request->get('amount'), $request->request->get('type'), $request->request->get('user_id'));

        $this->transactionService->runTransaction($transaction);

        return new JsonResponse($transaction);
    }

    public function archiveTransaction(Request $request, Response $response): Response {
        $this->transactionService->softDeleteTransaction($request->request->get('id'));

        return new JsonResponse(['message' => 'Transaction archived']);
    }
}
