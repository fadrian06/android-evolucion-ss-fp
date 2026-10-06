<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Repair;
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
          $blueprint->foreignIdFor(Repair::class)->constrained();
          $blueprint->decimal('amount_ves', 14, 2);
          $blueprint->string('method');
        }
      );
    }
  }
};
