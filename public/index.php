<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use App\Config\Container;
use App\Config\Router;

$request = Request::createFromGlobals();
$container = Container::getInstance();
$router = new Router($container);
$response = $router->handle($request);
$response->send();

















