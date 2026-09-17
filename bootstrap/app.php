<?php

declare(strict_types=1);

use Faslatam\PsrFramework\QueueRequestHandler;
use Faslatam\PsrFramework\RoutingMiddleware;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

$notFoundHandler = new class implements RequestHandlerInterface {
  #[Override]
  #[NoDiscard]
  public function handle(ServerRequestInterface $request): ResponseInterface
  {
    $responseFactory = new HttpFactory;

    return $responseFactory->createResponse(404);
  }
};

$middlewares = [];

foreach (glob(__DIR__ . '/../routes/*.php') as $routes) {
  $router = require_once $routes;
  $middlewares[] = new RoutingMiddleware($router);
}

$queueRequestHandler = new QueueRequestHandler(
  $notFoundHandler,
  ...$middlewares,
);

return $queueRequestHandler;
