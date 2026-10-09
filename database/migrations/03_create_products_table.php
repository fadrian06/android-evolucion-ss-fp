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
    if (!$builder->hasTable('products')) {
      $builder->create(
        'products',
        static function (Blueprint $blueprint): void {
          $user = new User;

          $blueprint->id();
          $blueprint->foreignIdFor($user::class)->constrained();
          $blueprint->string('name');
          $blueprint->enum('category', ['phone', 'accessory', 'spare_part']);
          $blueprint->decimal('price', 14, 2);
          $blueprint->unique([$user->getForeignKey(), 'name']);
        },
      );
    }
  }
};
