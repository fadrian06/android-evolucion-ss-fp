<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('businesses')) {
  Manager::schema()->create('businesses', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignId('user_id')->constrained();
    $blueprint->string('name');
    $blueprint->unique(['user_id', 'name']);
  });
}
