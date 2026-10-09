<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('items')) {
      $builder->create('items', static function (Blueprint $blueprint): void {
        $blueprint->id();
        $blueprint->foreignIdFor(Sale::class)->constrained();
        $blueprint->foreignIdFor(Product::class)->constrained();
        $blueprint->foreignIdFor(Business::class)->constrained();
        $blueprint->decimal('price', 14, 2);
        $blueprint->decimal('price_ves', 14, 2);
        $blueprint->integer('quantity');
        $blueprint->string('imei1')->nullable();
        $blueprint->string('imei2')->nullable();
        $blueprint->string('code')->nullable();
      });
    }
  }
};
