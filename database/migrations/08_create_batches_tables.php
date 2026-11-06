<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Batch;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    $batch = new Batch;

    if (!$builder->hasTable($batch->getTable())) {
      $builder->create(
        $batch->getTable(),
        static function (Blueprint $blueprint) use ($batch): void {
          if ($batch->getKeyType() === 'int') {
            $blueprint->unsignedBigInteger(
              $batch->getKeyName(),
              $batch->incrementing,
            );
          } else {
            $blueprint->string($batch->getKeyName())->primary();
          }

          $blueprint->foreignIdFor(Business::class)->constrained();
          $blueprint->foreignIdFor(Product::class)->constrained();
          $blueprint->integer('stock');
        },
      );
    }
  }
};
