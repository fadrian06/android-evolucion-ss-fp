<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('users')) {
  Manager::schema()->create('users', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->string('email')->unique();
    $blueprint->string('password')->unique();
  });
}

if (!Manager::schema()->hasTable('exchange_rates')) {
  Manager::schema()->create('exchange_rates', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignId('user_id')->constrained();
    $blueprint->date('date');
    $blueprint->decimal('rate', 12, 6);
    $blueprint->unique(['user_id', 'date']);
  });
}
