<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Layaway;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PayLayaway implements InvokableController
{
  public function __construct(
    private Business $business,
    private User $user,
    private DailyExchangeRate $dailyExchangeRate,
    private Form $form,
  )
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

    if ($layaway->getRemainingAmount() <= 0) {
      Flash::set(['El apartado ya está pagado'], 'errors');

      goto redirect;
    }

    $data = Flight::request()->data->getData();

    try {
      $customRate = $this->dailyExchangeRate->customRateFor($this->user);
      $payments = PaymentDetails::fromInputs(
        $data['amount'] ?? null,
        $data['method'] ?? null,
        $customRate ? (float) $customRate->rate : null,
      );
    } catch (\InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], 'errors');

      goto redirect;
    }

    if (empty($payments)) {
      Flash::set(['Debes ingresar al menos un pago'], 'errors');

      goto redirect;
    }

    if (PaymentDetails::totalUsd($payments) > $layaway->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    $layaway->payments()->createMany(PaymentDetails::forPersistence($payments));
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/apartados');
  }
}
