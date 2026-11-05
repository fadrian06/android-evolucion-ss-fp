<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Leaf\Auth;
use Leaf\Form;
use Leaf\Helpers\Password;
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
      'charset' => $_ENV['DB_CHARSET'],
      'collation' => 'utf8_unicode_ci',
      'prefix' => '',
    ]);

    $manager->setAsGlobal();
    $manager->bootEloquent();

    $isLocal = $_ENV['APP_ENV'] !== 'production';

    // Model::preventLazyLoading($isLocal);
    Model::preventSilentlyDiscardingAttributes($isLocal);

    return $manager;
  },
);

$container->singleton(
  Builder::class,
  static fn(): Builder => $container->get(Manager::class)::schema(),
);

$container->singleton(
  PDO::class,
  static fn(): PDO => $container->get(Manager::class)::connection()->getPdo(),
);

$container->singleton(Auth::class, static function () use ($container): Auth {
  $auth = new Auth;
  $user = new User;

  $auth->config([
    'id.key' => $user->getKeyName(),
    'db.table' => $user->getTable(),
    'timestamps' => false,
    'password.encode' => static fn(
      #[SensitiveParameter]
      string $password,
    ): string => (string) Password::hash($password, Password::BCRYPT, [
      'cost' => $_ENV['BCRYPT_ROUNDS'],
    ]),
    'password.verify' => Password::verify(...),
    'unique' => ['email'],
    'session' => true,
    'session.lifetime' => (int) $_ENV['SESSION_LIFETIME'],
    'redirect.login' => '/ingresar',
    'redirect.guest' => '/',
  ]);

  $auth->dbConnection($container->get(PDO::class));

  return $auth;
});

$container->singleton(Lingo::class, static function (): Lingo {
  $lingo = new Lingo;

  $lingo->create([
    'locales.default' => $_ENV['APP_LOCALE'],
    'locales.path' => __DIR__ . '/lang',
    'locales.strategy' => $_ENV['LOCALES_STRATEGY'],
  ]);

  return $lingo;
});

$container->singleton(Form::class, static function (): Form {
  $form = new Form;

  $form->rule('password', '/^.+$/', '{Field} must be a valid password');

  $form->rule(
    'min',
    static function (mixed $value, string $arg) use ($form): bool {
      if (is_numeric($value) && is_numeric($arg)) {
        $form->message('min', '{Field} must be at least %s');

        return $value >= $arg;
      }

      if (is_string($value)) {
        $form->message('min', '{Field} must be at least %s characters long');

        return strlen($value) >= $arg;
      }

      return false;
    },
  );

  $form->rule(
    'max',
    static function (mixed $value, string $arg) use ($form): bool {
      if (is_numeric($value) && is_numeric($arg)) {
        $form->message('max', '{Field} must not exceed %s');

        return $value <= $arg;
      }

      if (is_string($value)) {
        $form->message('max', '{Field} must not exceed %s characters');

        return strlen($value) <= $arg;
      }

      return false;
    },
  );

  $form->rule(
    'between',
    static function (mixed $value, string $args) use ($form): bool {
      [$min, $max] = explode(',', $args);

      if (is_numeric($value)) {
        $form->message('between', '{Field} must be between %s and %s');

        return $value >= $min && $value <= $max;
      }

      if (is_string($value)) {
        $form->message(
          'between',
          '{Field} must be between %s and %s characters long',
        );

        $valueLength = strlen($value);

        return $valueLength >= $min && $valueLength <= $max;
      }

      return false;
    },
  );

  $form->rule(
    'array',
    static function (
      mixed $value,
      array|string $args,
      string $field,
      array $data,
    ) use ($form): bool {
      if (!is_array($value)) {
        return false;
      }

      if (!$args || !is_string($args)) {
        return true;
      }

      $args = explode(',', $args);
      $argsLength = count($args);
      $keysRules = null;
      $valuesRules = null;

      if ($argsLength === 1) {
        $valuesRules = $args[0];
      } elseif ($argsLength === 2) {
        $keysRules = $args[0];
        $valuesRules = $args[1];
      } else {
        throw new InvalidArgumentException(
          'The "array" rule accepts at most two arguments',
        );
      }

      $test = new ReflectionMethod($form, 'test');

      foreach ($value as $keyToTest => $valueToTest) {
        if (
          $keysRules
          && !$test->invoke($form, $keysRules, $keyToTest, $field, $data)
        ) {
          return true;
        }

        if (!$test->invoke($form, $valuesRules, $valueToTest, $field, $data)) {
          return true;
        }
      }

      return true;
    },
    '{field} must be an array',
  );

  return $form;
});

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
