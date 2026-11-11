<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Builder;
use Leaf\Auth;
use Leaf\Form;
use Leaf\Http\Session;
use Leaf\Lingo;
use Psr\Container\ContainerInterface;

return static function (): ContainerInterface {
  $container = Container::getInstance();

  /** @var Closure(Container): Auth */
  $authFactory = require __DIR__ . '/auth.php';

  /** @var Closure(Container): Manager */
  $databaseFactory = require __DIR__ . '/database.php';

  /** @var Closure(): Lingo */
  $lingoFactory = require __DIR__ . '/lingo.php';

  /** @var Closure(): Form */
  $formFactory = require __DIR__ . '/form.php';

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
    $userId = $auth->id();

    return User::query()->findOrFail($userId);
  });

  $container->singleton(
    Business::class,
    static fn(): ?Business => $container
      ->get(User::class)
      ->businesses()
      ->getQuery()
      ->find((string) Session::get('business_id')),
  );

  return $container;
};
