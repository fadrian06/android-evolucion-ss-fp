<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Schema\Builder;

interface Migration
{
  public function up(Builder $builder): void;
}
