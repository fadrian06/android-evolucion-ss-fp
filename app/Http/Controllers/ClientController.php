<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class ClientController extends ResourceController
{
  public function __construct(private User $user, private Form $form)
  {
    //
  }

  #[Override]
  public function index(): void
  {
    Flight::render('clients', ['clients' => $this->user->clients], 'slot');
    Flight::render('components/layout');
  }

  #[Override]
  public function create(): void
  {
    throw new \Exception('Not implemented');
  }

  #[Override]
  public function store(): void
  {
    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'name' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($this->user->clients->contains('name', $validated['name'])) {
      Flash::set(['Ya existe un cliente con ese nombre'], 'errors');

      goto redirect;
    }

    $this->user->clients()->create($validated);
    Flash::set(['Cliente registrado'], 'successes');

    redirect:
    Flight::redirect('/clientes');
  }

  #[Override]
  public function show(string $id): void
  {
    throw new \Exception('Not implemented');
  }

  #[Override]
  public function edit(string $id): void
  {
    throw new \Exception('Not implemented');
  }

  #[Override]
  public function update(string $id): void
  {
    $client = $this->user->clients->find($id);

    if (!$client) {
      Flash::set(['Cliente no encontrado'], 'errors');

      goto redirect;
    }

    $validated = $this->form->validate(Flight::request()->data->getData()[$client->id], [
      'name' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($client->name === $validated['name']) {
      Flash::set(['El nombre del cliente no ha cambiado'], 'notes');

      goto redirect;
    }

    if ($this->user->clients->contains('name', Flight::request()->data['name'][$id])) {
      Flash::set(['Ya existe un cliente con ese nombre'], 'errors');

      goto redirect;
    }

    redirect:
    Flight::redirect('/clientes');
  }

  #[Override]
  public function destroy(string $id): void
  {
    $client = $this->user->clients->find($id);

    if (!$client) {
      Flash::set(['Cliente no encontrado'], 'errors');

      goto redirect;
    }

    $client->delete();
    Flash::set(['Cliente eliminado'], 'notes');

    redirect:
    Flight::redirect('/clientes');
  }
}
