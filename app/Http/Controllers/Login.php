<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use Flight;
use Leaf\Auth;
use Leaf\Form;
use Override;

final readonly class Login implements InvokableController
{
  public function __construct(private Auth $auth, private Form $form) {}

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'email' => 'email',
      'password' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), FlashKey::Errors);
      Flight::redirect('/ingresar');

      return;
    }

    if (!$this->auth->login($validated)) {
      Flash::set($this->auth->errors(), FlashKey::Errors);
      Flight::redirect('/ingresar');

      return;
    }

    Flash::set(['Sesión iniciada'], FlashKey::Successes);

    Flight::redirect('/');
  }
}
