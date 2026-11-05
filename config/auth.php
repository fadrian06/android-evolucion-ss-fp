<?php

declare(strict_types=1);

use App\Models\User;
use Leaf\Auth;
use Leaf\Helpers\Password;
use Psr\Container\ContainerInterface;

return static function (ContainerInterface $container): Auth {
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

  /** @var mixed */
  $pdo = $container->get(PDO::class);

  if ($pdo instanceof PDO) {
    $auth->dbConnection($pdo);
  }

  return $auth;
};
