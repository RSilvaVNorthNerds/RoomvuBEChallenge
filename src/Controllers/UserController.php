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

    public function createUser(Request $request, Response $response): Response {
        $user = new UserModel(
            name: $request->request->get('name'),
            credit: $request->request->get('credit')
        );

        $this->userService->createUser($user);

        return new JsonResponse($user);
    }

    public function populateFakeUsers(Request $request, Response $response): Response {
        $this->userService->populateFakeUsers($request->request->get('amount'));

        return new JsonResponse(['message' => 'Users populated']);
    }
}
