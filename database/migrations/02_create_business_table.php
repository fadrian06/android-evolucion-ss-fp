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
    if (!$builder->hasTable('businesses')) {
      $builder->create(
        'businesses',
        static function (Blueprint $blueprint): void {
          $user = new User;

          $blueprint->id();
          $blueprint->foreignIdFor($user::class)->constrained();
          $blueprint->string('name');
          $blueprint->string('rif')->unique();
          $blueprint->string('address');
          $blueprint->string('phone');
          $blueprint->unique([$user->getForeignKey(), 'name']);
        },
      );
    }
  }
};
