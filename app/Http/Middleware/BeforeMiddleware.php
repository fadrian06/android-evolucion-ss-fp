<?php

declare(strict_types=1);

namespace App\Http\Middleware;

interface BeforeMiddleware
{
  /** @return void|never */
  public function before(): void;
}
