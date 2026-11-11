<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\Batch;
use App\Models\Business;
use App\Models\Layaway;
use DateTimeImmutable;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Override;

final readonly class CancelLayaway implements InvokableController
{
  public function __construct(private Business $business) {}

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;
    $layaway = $this->business->layaways()->find($id);

    if (!$layaway instanceof Layaway) {
      Flash::set(['Apartado no encontrado'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    if ($layaway->cancelled_at) {
      Flash::set(['El apartado ya fue cancelado'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    if ($layaway->getRemainingAmount() <= 0) {
      Flash::set(['No se puede cancelar un apartado pagado'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    $batch = Batch::query()
      ->where('product_id', $layaway->product->id)
      ->where('business_id', $this->business->id)
      ->first();

    if (!$batch instanceof Batch) {
      Flash::set(
        ['No se encontró el lote original del producto apartado'],
        FlashKey::Errors,
      );

      Flight::redirect('/apartados');

      return;
    }

    Manager::connection()->transaction(static function () use (
      $layaway,
      $batch,
    ): void {
      $batch->stock++;
      $batch->save();
      $layaway->cancelled_at = new DateTimeImmutable;
      $layaway->save();
    });

    Flash::set(['Apartado cancelado y stock restaurado'], FlashKey::Notes);
    Flight::redirect('/apartados');
  }
}
