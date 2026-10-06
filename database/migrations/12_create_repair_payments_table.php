<?php

declare(strict_types=1);

use App\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('repair_payments')) {
      $builder->create(
        'repair_payments',
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignId('repair_id')->constrained();
          $blueprint->decimal('amount_ves', 14, 2);
          $blueprint->string('method');
        }
      );
    }
  }
};
