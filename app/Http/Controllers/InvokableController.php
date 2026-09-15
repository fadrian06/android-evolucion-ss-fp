<?php

declare(strict_types=1);

namespace App\Http\Controllers;

abstract readonly class InvokableController
{
  abstract public function __invoke(string ...$attributes): void;
}
