<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Layaway;
use App\Models\LayawayPayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    $layawayPayment = new LayawayPayment;

    if (!$builder->hasTable($layawayPayment->getTable())) {
      $builder->create(
        $layawayPayment->getTable(),
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Layaway::class)->constrained();
          $blueprint->decimal('amount');
          $blueprint->string('method');
          $blueprint->decimal('exchange_rate')->nullable();
        }
      );
    }
  }
};
