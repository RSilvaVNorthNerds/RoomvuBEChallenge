<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Models\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController {
    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function createUser(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        $name = filter_var($data['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $credit = (float) filter_var($data['credit'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if(!isset($data['name']) || !isset($data['credit'])) {
            return new JsonResponse([
                'error' => 'Missing required fields: name and credit are required'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        if(empty($name)) {
            return new JsonResponse([
                'error' => 'Name cannot be empty'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $user = new UserModel(
            name: $name,
            credit: $credit
        );

        $this->userService->createUser($user);

        return new JsonResponse([
            'name' => $user->getName(),
            'credit' => $user->getCredit()
        ]);
    }

    public function populateFakeUsers(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        $amount = (int) filter_var($data['amount'], FILTER_SANITIZE_NUMBER_INT);

        if(empty($amount)) {
            return new JsonResponse([
                'error' => 'Amount is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->userService->populateFakeUsers($amount);

        return new JsonResponse(['message' => 'Users populated']);
    }
}
