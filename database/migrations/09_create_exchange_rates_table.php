<?php

declare(strict_types=1);

use App\Migration;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    $exchangeRate = new ExchangeRate;

    if (!$builder->hasTable($exchangeRate->getTable())) {
      $builder->create(
        $exchangeRate->getTable(),
        static function (Blueprint $blueprint): void {
          $user = new User;

          $blueprint->id();
          $blueprint->foreignIdFor($user::class)->constrained();
          $blueprint->date('date');
          $blueprint->decimal('rate');
          $blueprint->unique([$user->getForeignKey(), 'date']);
        },
      );
    }
  }
};
