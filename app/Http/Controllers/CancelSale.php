<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Sale;
use DateTimeImmutable;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Leaf\Flash;
use Override;

final readonly class CancelSale implements InvokableController
{
  public function __construct(
    private Business $business,
  ) {}

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;
    $sale = $this->business->sales()->find($id);

    if (!$sale instanceof Sale) {
      Flash::set(['Comprobante no encontrado'], 'errors');
      Flight::redirect('/ventas');

      return;
    }

    if ($sale->cancelled_at) {
      Flash::set(['El comprobante ya fue anulado'], 'errors');
      Flight::redirect('/ventas');

      return;
    }

    $stockRestorations = [];

    foreach ($sale->items as $item) {
      $batch = Batch::query()
        ->where('product_id', $item->product->id)
        ->where('business_id', $item->business->id)
        ->first();

      if (!$batch instanceof Batch) {
        Flash::set(
          ['No se encontró el lote original de un ítem vendido'],
          'errors',
        );

        Flight::redirect('/ventas');

        return;
      }

      $stockRestorations[] = ['batch' => $batch, 'quantity' => $item->quantity];
    }

    Manager::connection()->transaction(static function () use (
      $sale,
      $stockRestorations,
    ): void {
      foreach ($stockRestorations as ['batch' => $batch, 'quantity' => $quantity]) {
        $batch->stock += $quantity;
        $batch->save();
      }

      $sale->cancelled_at = new DateTimeImmutable;
      $sale->save();
    });

    Flash::set(['Comprobante anulado y stock restaurado'], 'notes');
    Flight::redirect('/ventas');
  }
}
