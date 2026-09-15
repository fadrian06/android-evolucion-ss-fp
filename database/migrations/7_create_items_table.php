<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('items')) {
  Manager::schema()->create('items', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignIdFor(Sale::class)->constrained();
    $blueprint->foreignIdFor(Product::class)->constrained();
    $blueprint->integer('price');
    $blueprint->integer('quantity');
  });
}
