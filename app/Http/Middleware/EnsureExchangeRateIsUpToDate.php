<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\DailyExchangeRate;
use Flight;
use Override;

final readonly class EnsureExchangeRateIsUpToDate implements BeforeMiddleware
{
  public function __construct(
    private User $user,
    private DailyExchangeRate $dailyExchangeRate,
  ) {}

  #[Override]
  public function before(): void
  {
    $latestExchangeRate = $this->user->latestExchangeRate();
    $formattedLatestExchangeRate = $latestExchangeRate?->date->format('Y-m-d');
    $today = $this->dailyExchangeRate->today();
    $hasRateForToday = $formattedLatestExchangeRate === $today;

    Flight::view()->set(
      'exchangeRate',
      $hasRateForToday ? $latestExchangeRate : null,
    );

    Flight::view()->set('latestExchangeRate', $latestExchangeRate);

    if (
      str_starts_with(Flight::request()->url, '/calculadora')
      || Flight::request()->url === '/negocios'
    ) {
      return;
    }

    if (!$hasRateForToday) {
      Flight::redirect('/calculadora');

      return;
    }
  }
}
