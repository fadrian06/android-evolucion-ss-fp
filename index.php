<?php

declare(strict_types=1);

use App\Migration;
use Illuminate\Database\Schema\Builder;
use Leaf\Auth;
use Leaf\Lingo;
use Psr\Container\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

if (!file_exists($envFilePath = __DIR__ . '/.env')) {
  copy("{$envFilePath}.example", $envFilePath);
}

new Dotenv()->load("{$envFilePath}.example", $envFilePath);

if ($_ENV['DB_CONNECTION'] === 'sqlite' && !file_exists($_ENV['DB_DATABASE'])) {
  touch($_ENV['DB_DATABASE']);
}

date_default_timezone_set('America/Caracas');

/** @var Closure(): ContainerInterface */
$containerFactory = require __DIR__ . '/config/container.php';

$container = $containerFactory();

$migrationsFiles = (array) glob(__DIR__ . '/database/migrations/*.php');

foreach (array_filter($migrationsFiles) as $migrationFile) {
  $migration = (object) require_once $migrationFile;

  if ($migration instanceof Migration) {
    /** @var Builder */
    $builder = $container->get(Builder::class);

    $migration->up($builder);
  }
}

$routesFiles = (array) glob(__DIR__ . '/routes/*.php');

foreach (array_filter($routesFiles) as $routesFile) {
  require_once $routesFile;
}

Flight::set('flight.handle_errors', false);
Flight::set('flight.views.path', __DIR__ . '/resources/views');
Flight::view()->preserveVars = false;
Flight::view()->set('auth', $container->get(Auth::class));
Flight::view()->set('lingo', $container->get(Lingo::class));
Flight::view()->set('business', null);

Flight::registerContainerHandler(
  static fn(string $id): object => (object) $container->get($id),
);

Flight::start();
