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
