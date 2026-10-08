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

    try {
      $customRate = $this->dailyExchangeRate->customRateFor($this->user);
      $payment = PaymentDetails::fromInput(
        Flight::request()->data->amount,
        Flight::request()->data->method,
        $customRate ? (float) $customRate->rate : null,
      );
    } catch (\InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], 'errors');

      goto redirect;
    }

    if ($payment['amount_usd'] > $layaway->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    unset($payment['amount_usd']);
    $layaway->payments()->create($payment);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/apartados');
  }
}
