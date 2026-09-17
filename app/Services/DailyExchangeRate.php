<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\User;
use DateTimeImmutable;
use DateTimeZone;

final readonly class DailyExchangeRate
{
  public function __construct(private BcvExchangeRate $bcvExchangeRate)
  {
    //
  }

  public function customRateFor(User $user): ?ExchangeRate
  {
    return $user->exchangeRates()->where('date', $this->today())->first();
  }

  public function rateFor(User $user): float
  {
    $customRate = $this->customRateFor($user);

    return $customRate ? (float) $customRate->rate : $this->bcvExchangeRate->rate();
  }

  public function today(): string
  {
    return (new DateTimeImmutable('today', new DateTimeZone('America/Caracas')))->format('Y-m-d');
  }
}
