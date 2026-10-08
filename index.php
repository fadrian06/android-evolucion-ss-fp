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

(new Dotenv())->load("$envFilePath.example", $envFilePath);

if (
  $_ENV['DB_CONNECTION'] === 'sqlite'
  && !file_exists($_ENV['DB_DATABASE'])
) {
  touch($_ENV['DB_DATABASE']);
}

ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

$container = Container::getInstance();

$container->singleton(
  Manager::class,
  static function () use ($container): Manager {
    $manager = new Manager($container);

    $manager->addConnection([
      'driver' => $_ENV['DB_CONNECTION'],
      'host' => $_ENV['DB_HOST'],
      'database' => $_ENV['DB_DATABASE'],
      'username' => $_ENV['DB_USERNAME'],
      'password' => $_ENV['DB_PASSWORD'],
      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix' => '',
    ]);

    $manager->setAsGlobal();
    $manager->bootEloquent();

    return $manager;
  }
);

$container->singleton(
  Builder::class,
  static fn(): Builder => $container->get(Manager::class)::schema(),
);

$container->singleton(
  PDO::class,
  static fn(): PDO => $container->get(Manager::class)::connection()->getPdo(),
);

$container->singleton(
  Auth::class,
  static function () use ($container): Auth {
    $auth = new Auth;
    $auth->config('timestamps', false);
    $auth->config('unique', ['email', 'password']);
    $auth->config('session', true);
    $auth->dbConnection($container->get(PDO::class));

    return $auth;
  },
);

$container->singleton(Lingo::class, static function (): Lingo {
  $lingo = new Lingo;

  $lingo->create([
    'locales.default' => 'es',
    'locales.path' => __DIR__ . '/lang',
    'locales.strategy' => 'header',
  ]);

  return $lingo;
});

$container->singleton(Form::class, static function (): Form {
  $form = new Form;

  // TODO: Add form rules

  return $form;
});

$container->singleton(
  User::class,
  static function () use ($container): User {
    return User::query()->findOrFail($container->get(Auth::class)->id());
  },
);

$container->singleton(
  Business::class,
  static function () use ($container): ?Business {
    return $container
      ->get(User::class)
      ->businesses
      ->find(Session::get('business_id'));
  },
);

foreach (glob(__DIR__ . '/database/migrations/*.php') as $migrationFile) {
  $migration = require_once $migrationFile;

  if ($migration instanceof Migration) {
    $container->call($migration->up(...));
  }
}

foreach (glob(__DIR__ . '/routes/*.php') as $routes) {
  require_once $routes;
}

Flight::set('flight.handle_errors', false);
Flight::set('flight.views.path', __DIR__ . '/resources/views');
Flight::view()->preserveVars = false;
Flight::view()->set('auth', $container->get(Auth::class));
Flight::view()->set('lingo', $container->get(Lingo::class));
Flight::registerContainerHandler($container->get(...));
Flight::start();
