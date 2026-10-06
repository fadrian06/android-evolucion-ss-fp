<?php

declare(strict_types=1);

use App\Migration;
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
          $blueprint->foreignId('layaway_id')->constrained();
          $blueprint->integer('amount');
          $blueprint->string('method');
        }
      );
    }
  }
};
