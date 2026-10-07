<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Http\Session;
use Override;

final readonly class SelectBusiness implements InvokableController
{
  public function __construct(private User $user)
  {
    //
  }

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;

    $business = $this->user->businesses->find($id);

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
