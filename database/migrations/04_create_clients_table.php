<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    $client = new Client;

    if (!$builder->hasTable($client->getTable())) {
      $builder->create(
        $client->getTable(),
        static function (Blueprint $blueprint) use ($client): void {
          $user = new User;

          if ($client->getKeyType() === 'int') {
            $blueprint->unsignedBigInteger(
              $client->getKeyName(),
              $client->incrementing,
            );
          } else {
            $blueprint->string($client->getKeyName())->primary();
          }

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
