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
          $blueprint->id();
          $blueprint->foreignIdFor(User::class)->constrained();
          $blueprint->string('name');
          $blueprint->string('cedula');
          $blueprint->string('phone');
          $blueprint->string('address');
          $blueprint->unique(['user_id', 'name']);
        },
      );
    }
  }
};
