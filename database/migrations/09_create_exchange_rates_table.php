<?php

declare(strict_types=1);

use App\Migration;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('exchange_rates')) {
      $builder->create(
        'exchange_rates',
        static function (Blueprint $blueprint): void {
          $user = new User;

          $blueprint->id();
          $blueprint->foreignIdFor($user::class)->constrained();
          $blueprint->date('date');
          $blueprint->decimal('rate', 12, 6);
          $blueprint->unique([$user->getForeignKey(), 'date']);
        },
      );
    }
  }
};
