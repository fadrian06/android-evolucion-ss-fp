<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Business;
use Flight;
use Leaf\Auth;
use Leaf\Http\Session;
use Override;

final readonly class EnsureBusinessSelected implements BeforeMiddleware
{
  public function __construct(private Auth $auth)
  {
    //
  }

  #[Override]
  public function before(): void
  {
    $business = Business::query()->where('user_id', $this->auth->id())->find(Session::get('business_id'));

    if (!$business && Flight::request()->url !== '/negocios') {
      Flight::redirect('/negocios');

      exit;
    }

    Flight::view()->set('business', $business);
  }
}
