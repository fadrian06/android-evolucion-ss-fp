<?php

declare(strict_types=1);

use Leaf\Lingo;

return static function (): Lingo {
  $lingo = new Lingo;

  $lingo->create([
    'locales.default' => $_ENV['APP_LOCALE'],
    'locales.path' => __DIR__ . '/../lang',
    'locales.strategy' => $_ENV['LOCALES_STRATEGY'],
  ]);

  return $lingo;
};
