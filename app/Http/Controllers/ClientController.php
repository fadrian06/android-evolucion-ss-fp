<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Flight;
use Illuminate\Database\UniqueConstraintViolationException;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class ClientController implements ResourceController
{
  public function __construct(private User $user, private Form $form)
  {
    //
  }

  #[Override]
  public function index(): void
  {
    Flight::render('clients', [
      'clients' => $this->user->clients()
        ->withCount(['sales', 'repairs', 'layaways'])
        ->get(),
    ], 'slot');
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
      'id_card' => 'string',
      'phone' => 'string',
      'address' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($this->user->clients()->where('name', $validated['name'])->exists()) {
      Flash::set(['Ya existe un cliente con ese nombre'], 'errors');

      goto redirect;
    }

    if ($this->user->clients()->where('id_card', $validated['id_card'])->exists()) {
      Flash::set(['Ya existe un cliente con esa cédula'], 'errors');

      goto redirect;
    }

    try {
      $this->user->clients()->create($validated);
    } catch (UniqueConstraintViolationException $exception) {
      error_log($exception->getMessage());
      Flash::set(['No se pudo registrar el cliente porque ya existe un dato único'], 'errors');

      goto redirect;
    }

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

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'name' => 'string',
      'id_card' => 'string',
      'phone' => 'string',
      'address' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if (
      $client->name === $validated['name']
      && $client->id_card === $validated['id_card']
      && $client->phone === $validated['phone']
      && $client->address === $validated['address']
    ) {
      Flash::set(['Los datos del cliente no han cambiado'], 'notes');

      goto redirect;
    }

    if ($this->user->clients()
      ->where('name', $validated['name'])
      ->where('id', '!=', $client->id)
      ->exists()) {
      Flash::set(['Ya existe un cliente con ese nombre'], 'errors');

      goto redirect;
    }

    if ($this->user->clients()
      ->where('id_card', $validated['id_card'])
      ->where('id', '!=', $client->id)
      ->exists()) {
      Flash::set(['Ya existe un cliente con esa cédula'], 'errors');

      goto redirect;
    }

    try {
      $client->update($validated);
    } catch (UniqueConstraintViolationException $exception) {
      error_log($exception->getMessage());
      Flash::set(['No se pudo actualizar el cliente porque ya existe un dato único'], 'errors');

      goto redirect;
    }

    Flash::set(['Cliente actualizado'], 'successes');

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

    if ($client->sales()->exists() || $client->repairs()->exists() || $client->layaways()->exists()) {
      Flash::set(['No se puede eliminar un cliente que tiene registros asociados'], 'errors');

      goto redirect;
    }

    $client->delete();
    Flash::set(['Cliente eliminado'], 'notes');

    redirect:
    Flight::redirect('/clientes');
  }
}
