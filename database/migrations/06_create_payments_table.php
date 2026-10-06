<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Sale;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('payments')) {
      $builder->create(
        'payments',
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Sale::class)->constrained();
          $blueprint->integer('amount');
          $blueprint->string('method');
        }
      );
    }
  }
};
