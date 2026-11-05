<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Builder;
use Leaf\Auth;
use Leaf\Form;
use Leaf\Http\Session;
use Leaf\Lingo;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

if (!file_exists($envFilePath = __DIR__ . '/.env')) {
  copy("$envFilePath.example", $envFilePath);
}

new Dotenv()->load("$envFilePath.example", $envFilePath);

if ($_ENV['DB_CONNECTION'] === 'sqlite' && !file_exists($_ENV['DB_DATABASE'])) {
  touch($_ENV['DB_DATABASE']);
}

ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

/** @var Closure(Container): Auth */
$authFactory = require __DIR__ . '/config/auth.php';

/** @var Closure(Container): Manager */
$databaseFactory = require __DIR__ . '/config/database.php';

/** @var Closure(): Lingo */
$lingoFactory = require __DIR__ . '/config/lingo.php';

/** @var Closure(): Form */
$formFactory = require __DIR__ . '/config/form.php';

$container = Container::getInstance();

$container->singleton(Manager::class, $databaseFactory);

$container->singleton(
  Builder::class,
  static fn(): Builder => $container->get(Manager::class)::schema(),
);

$container->singleton(
  PDO::class,
  static fn(): PDO => $container->get(Manager::class)::connection()->getPdo(),
);

$container->singleton(Auth::class, $authFactory);
$container->singleton(Lingo::class, $lingoFactory);
$container->singleton(Form::class, $formFactory);

$container->singleton(User::class, static function () use ($container): User {
  $auth = $container->get(Auth::class);
  $id = $auth->id();

  return User::query()->findOrFail($id);
});

$container->singleton(
  Business::class,
  static function () use ($container): ?Business {
    $id = (int) Session::get('business_id');

    return $container->get(User::class)->businesses->find($id);
  },
);

$migrationsFiles = (array) glob(__DIR__ . '/database/migrations/*.php');

foreach (array_filter($migrationsFiles) as $migrationFile) {
  $migration = (object) require_once $migrationFile;

  if ($migration instanceof Migration) {
    $container->call($migration->up(...));
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
