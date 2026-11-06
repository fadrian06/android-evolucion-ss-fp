<?php

declare(strict_types=1);

use App\Migration;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    $user = new User;

    if (!$builder->hasTable($user->getTable())) {
      $builder->create(
        $user->getTable(),
        static function (Blueprint $blueprint) use ($user): void {
          if ($user->getKeyType() === 'int') {
            $blueprint->unsignedBigInteger(
              $user->getKeyName(),
              $user->incrementing,
            );
          } else {
            $blueprint->string($user->getKeyName())->primary();
          }

          $blueprint->string('email')->unique();
          $blueprint->string('password');
        },
      );
    }
  }
};
