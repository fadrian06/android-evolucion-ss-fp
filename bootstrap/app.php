<?php

declare(strict_types=1);

use App\RequestHandlers\NotFoundHandler;
use Faslatam\PsrFramework\QueueRequestHandler;
use Faslatam\PsrFramework\RoutingMiddleware;
use GuzzleHttp\Psr7\HttpFactory;

$middlewares = [];

foreach (glob(__DIR__ . '/../routes/*.php') as $routes) {
  $router = require_once $routes;
  $middlewares[] = new RoutingMiddleware($router);
}

$queueRequestHandler = new QueueRequestHandler(
  new NotFoundHandler(new HttpFactory),
  ...$middlewares,
);

return $queueRequestHandler;
