<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DailyExchangeRate;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class CalculatorController
{
  public function __construct(
    private User $user,
    private DailyExchangeRate $dailyExchangeRate,
    private Form $form,
  ) {
    //
  }

  public function index(): void
  {
    $rate = null;
    $source = null;
    $customRate = $this->dailyExchangeRate->customRateFor($this->user);

    try {
      $rate = $this->dailyExchangeRate->rateFor($this->user);
      $source = $customRate ? 'personalizada' : 'BCV';
    } catch (\RuntimeException $exception) {
      Flash::set([$exception->getMessage()], 'errors');
    }

    Flight::render('calculator', [
      'rate' => $rate,
      'source' => $source,
      'customRate' => $customRate,
    ], 'slot');
    Flight::render('components/layout');
  }

  public function store(): void
  {
    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'rate' => 'numeric',
    ]);

    if (!$validated || (float) $validated['rate'] <= 0) {
      Flash::set(
        $validated ? ['La cotización debe ser mayor que cero'] : $this->form->errors(),
        'errors',
      );
      Flight::redirect('/calculadora');

      return;
    }

    $this->user->exchangeRates()->updateOrCreate(
      ['date' => $this->dailyExchangeRate->today()],
      ['rate' => $validated['rate']],
    );
    Flash::set(['Cotización personalizada guardada'], 'successes');
    Flight::redirect('/calculadora');
  }
}
