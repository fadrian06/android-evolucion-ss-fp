<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\Business;
use App\Models\Layaway;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use Flight;
use InvalidArgumentException;
use Override;

final readonly class PayLayaway implements InvokableController
{
  public function __construct(
    private Business $business,
    private User $user,
    private DailyExchangeRate $dailyExchangeRate,
  ) {}

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
      Flash::set(['No se puede pagar un apartado cancelado'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    if ($layaway->getRemainingAmount() <= 0) {
      Flash::set(['El apartado ya está pagado'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    $data = Flight::request()->data->getData();

    try {
      $customRate = $this->dailyExchangeRate->customRateFor($this->user);

      $payments = PaymentDetails::fromInputs(
        $data['amount'] ?? null,
        $data['method'] ?? null,
        $customRate ? $customRate->rate : null,
      );
    } catch (InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    if (empty($payments)) {
      Flash::set(['Debes ingresar al menos un pago'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    if (PaymentDetails::totalUsd($payments) > $layaway->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], FlashKey::Errors);
      Flight::redirect('/apartados');

      return;
    }

    $layaway->payments()->createMany(PaymentDetails::forPersistence($payments));
    Flash::set(['Pago registrado'], FlashKey::Successes);
    Flight::redirect('/apartados');
  }
}
