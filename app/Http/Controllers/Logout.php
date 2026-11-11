<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Session;
use App\SessionKey;
use Flight;
use Leaf\Auth;
use Override;

final readonly class Logout implements InvokableController
{
  public function __construct(private Auth $auth) {}

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    $this->auth->logout();
    Session::unset(SessionKey::Business);
    Flash::set(['Sesión cerrada'], FlashKey::Successes);

    Flight::redirect('/ingresar');
  }
}
