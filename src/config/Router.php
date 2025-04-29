<?php

declare(strict_types=1);

namespace App\Config;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    private array $routes;
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routes = require __DIR__ . '/Routes.php';
    }

    public function handle(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPathInfo();

        $route = $this->routes[$method][$path] ?? null;

        if ($route) {
            [$controllerName, $methodName] = $route;
            $controller = $this->container->get($controllerName);
            return $controller->$methodName($request);
        }

        return new Response(
            json_encode(['error' => 'Not Found']),
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/json']
        );
    }
} 