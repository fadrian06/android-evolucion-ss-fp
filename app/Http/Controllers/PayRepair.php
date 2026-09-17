<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Repair;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PayRepair implements InvokableController
{
  public function __construct(private Business $business, private Form $form)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;
    $repair = $this->business->repairs->find($id);

    if (!$repair instanceof Repair) {
      Flash::set(['Reparación no encontrada'], 'errors');

      goto redirect;
    }

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'amount_ves' => 'numeric',
      'method' => 'in:[Físico,Punto,Transferencia]',
    ]);

    if (!$validated || (float) $validated['amount_ves'] <= 0) {
      Flash::set(
        $validated ? ['El monto debe ser mayor que cero'] : $this->form->errors(),
        'errors',
      );

      goto redirect;
    }

    if ($repair->getRemainingAmountVes() <= 0) {
      Flash::set(['La reparación ya está pagada'], 'errors');

      goto redirect;
    }

    if ((float) $validated['amount_ves'] > $repair->getRemainingAmountVes()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    $repair->payments()->create($validated);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/reparaciones');
  }
}
