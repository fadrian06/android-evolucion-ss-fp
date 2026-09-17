<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use Flight;
use Leaf\Flash;
use Leaf\Http\Session;
use Override;

final readonly class SelectBusiness implements InvokableController
{
  #[Override]
  public function __invoke(string ...$attributes): void
  {
    [$id] = $attributes;

    $business = Business::query()->find($id);

    Session::set('business_id', $business->id);
    Flash::set(["Has seleccionado el negocio: $business->name"], 'successes');

    Flight::redirect('/negocios');
  }
}
