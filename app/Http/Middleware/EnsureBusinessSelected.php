<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Business;
use Flight;
use Override;

final readonly class EnsureBusinessSelected implements BeforeMiddleware
{
  public function __construct(private ?Business $business)
  {
    //
  }

  #[Override]
  public function before(): void
  {
    if (!$this->business && Flight::request()->url !== '/negocios') {
      Flight::redirect('/negocios');

      exit;
    }

    Flight::view()->set('business', $this->business);
  }
}
