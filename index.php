<?php

declare(strict_types=1);

use App\Migration;
use Illuminate\Database\Schema\Builder;
use Leaf\Auth;
use Leaf\Lingo;
use Psr\Container\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;

use function App\foreach_file;

require_once __DIR__ . '/vendor/autoload.php';

new Dotenv()->load(__DIR__ . '/.env');

/** @var Closure(): ContainerInterface */
$containerFactory = require __DIR__ . '/config/container.php';

$container = $containerFactory();
$containerHandler = fn(string $id): object => (object) $container->get($id);

/** @var Builder */
$builder = $container->get(Builder::class);

foreach_file(
  __DIR__ . '/database/migrations/*.php',
  static function (string $file) use ($builder): void {
    $migration = (object) require_once $file;

    if ($migration instanceof Migration) {
      $migration->up($builder);
    }
  },
);

foreach_file(__DIR__ . '/routes/*.php', static function (string $file): void {
  require_once $file;
});

Flight::set('flight.handle_errors', value: false);
Flight::view()->path = __DIR__ . '/resources/views';
Flight::view()->preserveVars = false;
Flight::view()->set('auth', $container->get(Auth::class));
Flight::view()->set('lingo', $container->get(Lingo::class));
Flight::view()->set('business', value: null);
Flight::registerContainerHandler($containerHandler);

Flight::start();
