<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;

return static function (Container $container): Manager {
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

  Model::shouldBeStrict($_ENV['APP_ENV'] !== 'production');

  return $manager;
};
