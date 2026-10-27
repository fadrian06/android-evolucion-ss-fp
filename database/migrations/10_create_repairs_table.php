<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Business;
use App\Models\Client;
use App\Models\Repair;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration
{
  #[Override]
  public function up(Builder $builder): void
  {
    $repair = new Repair;

    if (!$builder->hasTable($repair->getTable())) {
      $builder->create(
        $repair->getTable(),
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Business::class)->constrained();
          $blueprint->foreignIdFor(Client::class)->constrained();
          $blueprint->string('description');
          $blueprint->integer('price');
          $blueprint->decimal('price_ves');
          $blueprint->date('due_date');
          $blueprint->timestamps();
        },
      );
    }
  }
};
