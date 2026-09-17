<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PaySale implements InvokableController
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
      'amount' => 'number',
      'method' => 'in:[Físico,Punto,Transferencia]',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ((int) $validated['amount'] <= 0) {
      Flash::set(['El monto debe ser mayor que cero'], 'errors');

      goto redirect;
    }

    if ($sale->getRemainingAmount() <= 0) {
      Flash::set(['La venta ya está pagada'], 'errors');

      goto redirect;
    }

    if ($validated['amount'] > $sale->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    $sale->payments()->create($validated);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/ventas');
  }
}
