<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\User;
use App\Session;
use App\SessionKey;
use Flight;
use Override;

final readonly class SelectBusiness implements InvokableController
{
  public function __construct(private User $user) {}

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;

    $business = $this->user->businesses->find($id);

    if (!$business) {
      Flash::set(['Negocio no encontrado'], FlashKey::Errors);
      Flight::redirect('/negocios');

      return;
    }

    Session::set(SessionKey::Business, $business->id);

    Flash::set(
      ["Has seleccionado el negocio: {$business->name}"],
      FlashKey::Successes,
    );

    Flight::redirect('/negocios');
  }
}
