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

if (!Manager::schema()->hasTable('repair_payments')) {
  Manager::schema()->create(
    'repair_payments',
    static function (Blueprint $blueprint): void {
      $blueprint->id();
      $blueprint->foreignId('repair_id')->constrained();
      $blueprint->decimal('amount_ves', 14, 2);
      $blueprint->string('method');
    }
  );
}

if (!Manager::schema()->hasTable('layaway_payments')) {
  Manager::schema()->create(
    'layaway_payments',
    static function (Blueprint $blueprint): void {
      $blueprint->id();
      $blueprint->foreignId('layaway_id')->constrained();
      $blueprint->integer('amount');
      $blueprint->string('method');
    }
  );
}
