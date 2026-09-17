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
  public function __construct(private Business $business)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;
    $sale = $this->business->sales()->find($id);

    if (!$sale instanceof Sale) {
      Flash::set(['Factura no encontrada'], 'errors');

      goto redirect;
    }

    if ($sale->cancelled_at) {
      Flash::set(['La factura ya fue anulada'], 'errors');

      goto redirect;
    }

    $stockRestorations = [];

    foreach ($sale->items as $item) {
      $batch = Batch::query()
        ->where('product_id', $item->product_id)
        ->where('business_id', $item->business_id)
        ->first();

      if (!$batch instanceof Batch) {
        Flash::set(['No se encontró el lote original de un ítem facturado'], 'errors');

        goto redirect;
      }

      $stockRestorations[] = ['batch' => $batch, 'quantity' => $item->quantity];
    }

    Manager::connection()->transaction(function () use ($sale, $stockRestorations): void {
      foreach ($stockRestorations as ['batch' => $batch, 'quantity' => $quantity]) {
        $batch->stock += $quantity;
        $batch->save();
      }

      $sale->cancelled_at = new DateTimeImmutable;
      $sale->save();
    });

    Flash::set(['Factura anulada y stock restaurado'], 'notes');

    redirect:
    Flight::redirect('/ventas');
  }
}
