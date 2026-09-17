<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Flight;
use Override;

final readonly class ShowDashboardPage implements InvokableController
{
  #[Override]
  public function __invoke(string ...$attributes): void
  {
    Flight::render('components/layout', ['slot' => '']);
  }
}
