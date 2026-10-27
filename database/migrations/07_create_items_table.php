<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\Item;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    $item = new Item;

    if (!$builder->hasTable($item->getTable())) {
      $builder->create(
        $item->getTable(),
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Sale::class)->constrained();
          $blueprint->foreignIdFor(Product::class)->constrained();
          $blueprint->foreignIdFor(Business::class)->constrained();
          $blueprint->decimal('price');
          $blueprint->decimal('price_ves');
          $blueprint->integer('quantity');
          $blueprint->string('imei1')->nullable();
          $blueprint->string('imei2')->nullable();
          $blueprint->string('code')->nullable();
        },
      );
    }
  }
};
