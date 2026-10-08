<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PaySale implements InvokableController
{
  public function __construct(
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

    if ($sale->cancelled_at) {
      Flash::set(['No se puede pagar un comprobante anulado'], 'errors');

      goto redirect;
    }

    if ($sale->getRemainingAmount() <= 0) {
      Flash::set(['La venta ya está pagada'], 'errors');

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

    if ($payment['amount_usd'] > $sale->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    unset($payment['amount_usd']);
    $sale->payments()->create($payment);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/ventas');
  }
}
