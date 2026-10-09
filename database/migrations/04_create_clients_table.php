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
    if (!$builder->hasTable('clients')) {
      $builder->create(
        'clients',
        static function (Blueprint $blueprint): void {
          $user = new User;

          $blueprint->id();
          $blueprint->foreignIdFor($user::class)->constrained();
          $blueprint->string('name');
          $blueprint->string('id_card');
          $blueprint->string('phone');
          $blueprint->string('address');
          $blueprint->unique([$user->getForeignKey(), 'name']);
          $blueprint->unique([$user->getForeignKey(), 'id_card']);
        },
      );
    }
  }
};
