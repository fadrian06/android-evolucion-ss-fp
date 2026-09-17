<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\User;
use flight\net\Request;
use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Leaf\Auth;
use Leaf\Db;
use Leaf\Form;
use Leaf\Http\Session;
use Leaf\Lingo;
use Symfony\Component\Dotenv\Dotenv;

use function Faslatam\PsrFramework\sendResponse;

require_once __DIR__ . '/vendor/autoload.php';

$requestHandler = require_once __DIR__ . '/bootstrap/app.php';
$serverRequest = ServerRequest::fromGlobals();
$response = $requestHandler->handle($serverRequest);
sendResponse($response);

(new Dotenv())->load(__DIR__ . '/.env');

if ($_ENV['DB_CONNECTION'] === 'sqlite' && !file_exists($_ENV['DB_DATABASE'])) {
  touch($_ENV['DB_DATABASE']);
}

ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

$container = Container::getInstance();

$manager = new Manager($container);

$manager->addConnection([
  'driver' => $_ENV['DB_CONNECTION'],
  'host' => 'localhost',
  'database' => $_ENV['DB_DATABASE'],
  'username' => 'root',
  'password' => '',
  'charset' => 'utf8',
  'collation' => 'utf8_unicode_ci',
  'prefix' => '',
]);

$manager->setAsGlobal();
$manager->bootEloquent();
$pdo = $manager::connection()->getPdo();
$auth = new Auth;
$auth->config('timestamps', false);
$auth->config('unique', ['email', 'password']);
$auth->config('session', true);
$auth->dbConnection($pdo);
$db = $auth->db();
$lingo = new Lingo;

$lingo->create([
  'locales.default' => 'es',
  'locales.path' => __DIR__ . '/lang',
  'locales.strategy' => 'header',
]);

$form = new Form;

$container->singleton(Manager::class, static fn(): Manager => $manager);
$container->singleton(Auth::class, static fn(): Auth => $auth);
$container->singleton(Db::class, static fn(): Db => $db);
$container->singleton(PDO::class, static fn(): PDO => $pdo);

$container->singleton(
  Request::class,
  static fn(): Request => Flight::request(),
);

$container->singleton(
  Connection::class,
  static fn(): Connection => $manager::connection(),
);

$container->singleton(Form::class, static fn(): Form => $form);

foreach (glob(__DIR__ . '/database/migrations/*.php') as $migration) {
  require_once $migration;
}

$user = User::query()->find($auth->id());

$container->singleton(User::class, static fn(): User => $user);

$businessId = Session::get('business_id');

$container->singleton(
  Business::class,
  static fn(): Business => $user->businesses->find($businessId)
);

foreach (glob(__DIR__ . '/routes/*.php') as $routes) {
  require_once $routes;
}

Flight::set('flight.handle_errors', false);
Flight::set('flight.views.path', __DIR__ . '/resources/views');
Flight::view()->preserveVars = false;
Flight::view()->set('auth', $auth);
Flight::view()->set('lingo', $lingo);
Flight::registerContainerHandler($container->get(...));
Flight::start();
