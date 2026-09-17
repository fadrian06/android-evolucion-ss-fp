<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('businesses')) {
  Manager::schema()->create('businesses', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignIdFor(User::class)->constrained();
    $blueprint->string('name');
    $blueprint->string('rif');
    $blueprint->string('address');
    $blueprint->string('phone');
    $blueprint->unique(['user_id', 'name']);
  });
}
