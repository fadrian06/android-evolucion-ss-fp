<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Flight;
use Leaf\Auth;
use Override;

final readonly class RedirectIfAuthenticated implements BeforeMiddleware
{
  public function __construct(private Auth $auth)
  {
    //
  }

  #[Override]
  public function before(): void
  {
    if ($this->auth->user()) {
      Flight::redirect('/');

      exit;
    }
  }
}
