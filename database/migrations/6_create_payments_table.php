<?php

declare(strict_types=1);

use App\Models\Sale;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('payments')) {
  Manager::schema()->create(
    'payments',
    static function (Blueprint $blueprint): void {
      $blueprint->id();
      $blueprint->foreignIdFor(Sale::class)->constrained();
      $blueprint->integer('amount');
      $blueprint->string('method');
    }
  );
}
