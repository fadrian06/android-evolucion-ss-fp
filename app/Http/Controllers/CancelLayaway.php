<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Layaway;
use DateTimeImmutable;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Leaf\Flash;
use Override;

final readonly class CancelLayaway implements InvokableController
{
  public function __construct(private Business $business)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;
    $layaway = $this->business->layaways()->find($id);

    if (!$layaway instanceof Layaway) {
      Flash::set(['Apartado no encontrado'], 'errors');

      goto redirect;
    }

    if ($layaway->cancelled_at) {
      Flash::set(['El apartado ya fue cancelado'], 'errors');

      goto redirect;
    }

    $batch = Batch::query()
      ->where('product_id', $layaway->product_id)
      ->where('business_id', $this->business->id)
      ->first();

    if (!$batch instanceof Batch) {
      Flash::set(['No se encontró el lote original del teléfono apartado'], 'errors');

      goto redirect;
    }

    Manager::connection()->transaction(function () use ($layaway, $batch): void {
      $batch->stock++;
      $batch->save();
      $layaway->cancelled_at = new DateTimeImmutable;
      $layaway->save();
    });

    Flash::set(['Apartado cancelado y stock restaurado'], 'notes');

    redirect:
    Flight::redirect('/apartados');
  }
}
