<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('clients')) {
  Manager::schema()->create('clients', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignIdFor(User::class)->constrained();
    $blueprint->string('name');
    $blueprint->unique(['user_id', 'name']);
  });
}
