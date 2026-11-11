<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\Sale;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use Flight;
use InvalidArgumentException;
use Override;

final readonly class PaySale implements InvokableController
{
  public function __construct(
    private User $user,
    private DailyExchangeRate $dailyExchangeRate,
  ) {}

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
      Flash::set(['Venta no encontrada'], FlashKey::Errors);
      Flight::redirect('/ventas');

      return;
    }

    if ($sale->cancelled_at) {
      Flash::set(['No se puede pagar un comprobante anulado'], FlashKey::Errors);
      Flight::redirect('/ventas');

      return;
    }

    if ($sale->getRemainingAmount() <= 0) {
      Flash::set(['La venta ya está pagada'], FlashKey::Errors);
      Flight::redirect('/ventas');

      return;
    }

    try {
      $customRate = $this->dailyExchangeRate->customRateFor($this->user);

      $payment = PaymentDetails::fromInput(
        Flight::request()->data->amount,
        Flight::request()->data->method,
        $customRate ? (float) $customRate->rate : null,
      );
    } catch (InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], FlashKey::Errors);
      Flight::redirect('/ventas');

      return;
    }

    if ($payment['amount_usd'] > $sale->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], FlashKey::Errors);
      Flight::redirect('/ventas');

      return;
    }

    unset($payment['amount_usd']);
    $sale->payments()->create($payment);
    Flash::set(['Pago registrado'], FlashKey::Successes);
    Flight::redirect('/ventas');
  }
}
