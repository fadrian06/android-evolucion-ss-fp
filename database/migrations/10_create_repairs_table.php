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
    if (!$builder->hasTable('repairs')) {
      $builder->create(
        'repairs',
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Business::class)->constrained();
          $blueprint->foreignIdFor(Client::class)->constrained();
          $blueprint->string('description');
          $blueprint->integer('price');
          $blueprint->decimal('price_ves', 14, 2);
          $blueprint->date('due_date');
          $blueprint->timestamps();
        },
      );
    }
  }
};
