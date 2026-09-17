<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BcvExchangeRate;
use App\Models\User;
use DateTimeImmutable;
use DateTimeZone;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class CalculatorController
{
  public function __construct(
    private User $user,
    private BcvExchangeRate $bcvExchangeRate,
    private Form $form,
  ) {
    //
  }

  public function index(): void
  {
    $rate = null;
    $source = null;
    $customRate = $this->user->exchangeRates()->where('date', $this->today())->first();

    if ($customRate) {
      $rate = (float) $customRate->rate;
      $source = 'personalizada';
    } else {
      try {
        $rate = $this->bcvExchangeRate->rate();
        $source = 'BCV';
      } catch (\RuntimeException $exception) {
        Flash::set([$exception->getMessage()], 'errors');
      }
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
      ['date' => $this->today()],
      ['rate' => $validated['rate']],
    );
    Flash::set(['Cotización personalizada guardada'], 'successes');
    Flight::redirect('/calculadora');
  }

  private function today(): string
  {
    return (new DateTimeImmutable('today', new DateTimeZone('America/Caracas')))->format('Y-m-d');
  }
}
