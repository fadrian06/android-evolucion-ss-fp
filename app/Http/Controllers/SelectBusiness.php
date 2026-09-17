<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use Flight;
use Leaf\Auth;
use Leaf\Flash;
use Leaf\Http\Session;
use Override;

final readonly class SelectBusiness implements InvokableController
{
  public function __construct(private Auth $auth)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;

    $business = Business::query()->where('user_id', $this->auth->id())->find($id);

    if (!$business) {
      Flash::set(['Negocio no encontrado'], 'errors');
      Flight::redirect('/negocios');

      return;
    }

    Session::set('business_id', $business->id);
    Flash::set(["Has seleccionado el negocio: $business->name"], 'successes');

    Flight::redirect('/negocios');
  }
}
