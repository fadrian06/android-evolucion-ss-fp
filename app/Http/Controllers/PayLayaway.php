<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Layaway;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PayLayaway implements InvokableController
{
  public function __construct(private Business $business, private Form $form)
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
      Flash::set(['No se puede pagar un apartado cancelado'], 'errors');

      goto redirect;
    }

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'amount' => 'number',
      'method' => 'in:[Físico,Punto,Transferencia]',
    ]);

    if (!$validated || (int) $validated['amount'] <= 0) {
      Flash::set($validated ? ['El monto debe ser mayor que cero'] : $this->form->errors(), 'errors');

      goto redirect;
    }

    if ($layaway->getRemainingAmount() <= 0) {
      Flash::set(['El apartado ya está pagado'], 'errors');

      goto redirect;
    }

    if ((int) $validated['amount'] > $layaway->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    $layaway->payments()->create($validated);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/apartados');
  }
}
