<?php

declare(strict_types=1);

namespace App\Http\Controllers;

interface InvokableController
{
  public function __invoke(string ...$attributes): void;
}
