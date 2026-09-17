<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BcvExchangeRate;
use Flight;
use Override;

final readonly class BcvExchangeRateController implements InvokableController
{
  public function __construct(private BcvExchangeRate $bcvExchangeRate)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    try {
      Flight::json($this->bcvExchangeRate->fetch());
    } catch (\RuntimeException $exception) {
      error_log($exception->getMessage());
      Flight::halt(503);
    }
  }
}
