<?php

declare(strict_types=1);

use App\Migration;
use App\Models\Repair;
use App\Models\RepairPayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new readonly class implements Migration {
  #[Override]
  public function up(Builder $builder): void
  {
    $repairPayment = new RepairPayment;

    if (!$builder->hasTable($repairPayment->getTable())) {
      $builder->create(
        $repairPayment->getTable(),
        static function (Blueprint $blueprint) use ($repairPayment): void {
          if ($repairPayment->getKeyType() === 'int') {
            $blueprint->unsignedBigInteger(
              $repairPayment->getKeyName(),
              $repairPayment->incrementing,
            );
          } else {
            $blueprint->string($repairPayment->getKeyName())->primary();
          }

          $blueprint->foreignIdFor(Repair::class)->constrained();
          $blueprint->decimal('amount');
          $blueprint->string('method');
          $blueprint->decimal('exchange_rate')->nullable();
        },
      );
    }
  }
};
