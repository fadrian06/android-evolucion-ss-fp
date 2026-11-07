<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use Flight;
use Leaf\Auth;
use Leaf\Form;
use Override;

final readonly class Register implements InvokableController
{
  public function __construct(private Auth $auth, private Form $form) {}

  #[Override]
  public function __invoke(string ...$attributes): void
  {
    $passwordKey = (string) $this->auth->config('password.key');

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'email' => 'email',
      $passwordKey => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), FlashKey::Errors);
      Flight::redirect('/registrarse');

      return;
    }

    if (!$this->auth->register($validated)) {
      Flash::set($this->auth->errors(), FlashKey::Errors);
      Flight::redirect('/registrarse');

      return;
    }

    Flash::set(['Has sido registrado'], FlashKey::Successes);
    Flight::redirect('/');
  }
}
