<?php

declare(strict_types=1);

use App\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('users')) {
      $builder->create('users', static function (Blueprint $blueprint): void {
        $blueprint->id();
        $blueprint->string('email')->unique();
        $blueprint->string('password');
      });
    }
  }
};
