<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Flight;
use Leaf\Auth;
use Leaf\Flash;
use Leaf\Http\Session;
use Override;

final readonly class Logout implements InvokableController
{
  public function __construct(private Auth $auth)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    $this->auth->logout();
    Session::unset('business_id');
    Flash::set(['Sesión cerrada'], 'successes');

    Flight::redirect('/ingresar');
  }
}
