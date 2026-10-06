<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Layaway;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('layaway_payments')) {
      $builder->create(
        'layaway_payments',
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Layaway::class)->constrained();
          $blueprint->integer('amount');
          $blueprint->string('method');
        }
      );
    }
  }
};
