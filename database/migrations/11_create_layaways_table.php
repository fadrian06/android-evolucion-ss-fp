<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('layaways')) {
      $builder->create(
        'layaways',
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Business::class)->constrained();
          $blueprint->foreignIdFor(Client::class)->constrained();
          $blueprint->foreignIdFor(Product::class)->constrained();
          $blueprint->integer('price');
          $blueprint->decimal('price_ves', 14, 2);
          $blueprint->string('imei1');
          $blueprint->string('imei2');
          $blueprint->timestamp('cancelled_at')->nullable();
          $blueprint->timestamps();
        }
      );
    }
  }
};
