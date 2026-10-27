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
    $user = new User;

    if (!$builder->hasTable($user->getTable())) {
      $builder->create(
        $user->getTable(),
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->string('email')->unique();
          $blueprint->string('password');
        },
      );
    }
  }
};
