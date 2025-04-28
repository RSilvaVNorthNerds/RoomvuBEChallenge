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
        
        $user = new UserModel(
            name: $data['name'],
            credit: $data['credit']
        );

        $this->userService->createUser($user);

        return new JsonResponse($user);
    }

    public function populateFakeUsers(Request $request): Response {
        $this->userService->populateFakeUsers($request->request->get('amount'));

        return new JsonResponse(['message' => 'Users populated']);
    }
}
