<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    $payment = new Payment;

    if (!$builder->hasTable($payment->getTable())) {
      $builder->create(
        $payment->getTable(),
        static function (Blueprint $blueprint): void {
          $blueprint->id();
          $blueprint->foreignIdFor(Sale::class)->constrained();
          $blueprint->decimal('amount');
          $blueprint->string('method');
          $blueprint->decimal('exchange_rate')->nullable();
        }
      );
    }
  }
};
