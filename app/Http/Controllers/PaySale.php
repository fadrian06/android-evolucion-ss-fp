<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PaySale extends InvokableController
{
  public function __construct(private User $user, private Form $form)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;
    $sale = null;

    foreach ($this->user->businesses as $business) {
      $sale = $business->sales->find($id);

      if ($sale) {
        break;
      }
    }

    if (!$sale instanceof Sale) {
      Flash::set(['Venta no encontrada'], 'errors');

      goto redirect;
    }

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'amount' => 'integer',
      'method' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    $sale->payments()->create($validated);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/ventas');
  }
}
