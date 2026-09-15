<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('batches')) {
  Manager::schema()->create('batches', static function (Blueprint $blueprint): void {
    $blueprint->id();
    $blueprint->foreignIdFor(Business::class)->constrained();
    $blueprint->foreignIdFor(Product::class)->constrained();
    $blueprint->integer('stock');
  });
}
