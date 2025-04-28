<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Models\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController {
    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function createUser(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        $name = filter_var($data['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $credit = (float) filter_var($data['credit'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if(empty($name) || empty($credit)) {
            return new JsonResponse([
                'error' => 'Missing required fields: name and credit are required'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $user = new UserModel(
            name: $name,
            credit: $credit
        );

        $this->userService->createUser($user);

        return new JsonResponse($user);
    }

    public function populateFakeUsers(Request $request): Response {
        $this->userService->populateFakeUsers($request->request->get('amount'));

        return new JsonResponse(['message' => 'Users populated']);
    }
}
