<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Repair;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class PayRepair implements InvokableController
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
    $repair = $this->business->repairs->find($id);

    if (!$repair instanceof Repair) {
      Flash::set(['Reparación no encontrada'], 'errors');

      goto redirect;
    }

    if ($repair->getRemainingAmount() <= 0) {
      Flash::set(['La reparación ya está pagada'], 'errors');

      goto redirect;
    }

    try {
      $customRate = $this->dailyExchangeRate->customRateFor($this->user);
      $data = Flight::request()->data->getData();
      $payment = PaymentDetails::fromInput(
        $data['amount'] ?? null,
        $data['method'] ?? null,
        $customRate ? (float) $customRate->rate : null,
      );
    } catch (\InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], 'errors');

      goto redirect;
    }

    if ($payment['amount_usd'] > $repair->getRemainingAmount()) {
      Flash::set(['El pago excede el saldo pendiente'], 'errors');

      goto redirect;
    }

    unset($payment['amount_usd']);
    $repair->payments()->create($payment);
    Flash::set(['Pago registrado'], 'successes');

    redirect:
    Flight::redirect('/reparaciones');
  }
}
