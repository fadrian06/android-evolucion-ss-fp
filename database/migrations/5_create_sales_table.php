<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\Client;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('sales')) {
  Manager::schema()->create('sales', static function (Blueprint $blueprint) {
    $blueprint->id();
    $blueprint->foreignIdFor(Business::class)->constrained();
    $blueprint->foreignIdFor(Client::class)->constrained();
    $blueprint->timestamp('cancelled_at')->nullable();
    $blueprint->timestamps();
  });
}

if (!Manager::schema()->hasTable('repairs')) {
  Manager::schema()->create('repairs', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignIdFor(Business::class)->constrained();
    $blueprint->foreignIdFor(Client::class)->constrained();
    $blueprint->string('description');
    $blueprint->integer('price');
    $blueprint->decimal('price_ves', 14, 2);
    $blueprint->date('due_date');
    $blueprint->timestamps();
  });
}

if (!Manager::schema()->hasTable('layaways')) {
  Manager::schema()->create('layaways', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignIdFor(Business::class)->constrained();
    $blueprint->foreignIdFor(Client::class)->constrained();
    $blueprint->foreignIdFor(\App\Models\Product::class)->constrained();
    $blueprint->integer('price');
    $blueprint->decimal('price_ves', 14, 2);
    $blueprint->string('imei1');
    $blueprint->string('imei2');
    $blueprint->timestamp('cancelled_at')->nullable();
    $blueprint->timestamps();
  });
}
