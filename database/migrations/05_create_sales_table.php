<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    if (!$builder->hasTable('sales')) {
      $builder->create('sales', static function (Blueprint $blueprint) {
        $blueprint->id();
        $blueprint->foreignIdFor(Business::class)->constrained();
        $blueprint->foreignIdFor(Client::class)->constrained();
        $blueprint->timestamp('cancelled_at')->nullable();
        $blueprint->timestamps();
      });
    }
  }
};
