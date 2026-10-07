<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Flight;
use Override;

final readonly class ShowRegisterPage implements InvokableController
{
  #[Override]
  public function __invoke(string ...$attributes): void
  {
    Flight::render('register', [], 'slot');
    Flight::render('components/layout');
  }
}
