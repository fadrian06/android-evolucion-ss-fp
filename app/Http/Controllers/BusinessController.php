<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class BusinessController implements ResourceController
{
  public function __construct(private User $user, private Form $form)
  {
    //
  }

  #[Override]
  public function index(): void
  {
    Flight::render('businesses', [
      'businesses' => $this->user->businesses,
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
      'rif' => 'string',
      'address' => 'string',
      'phone' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($this->user->businesses->contains('name', $validated['name'])) {
      Flash::set(['Ya existe un negocio con ese nombre'], 'errors');

      goto redirect;
    }

    $this->user->businesses()->create($validated);
    Flash::set(['Negocio registrado'], 'successes');

    redirect:
    Flight::redirect('/negocios');
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
    $business = $this->user->businesses->find($id);

    if (!$business) {
      Flash::set(['Negocio no encontrado'], 'errors');

      goto redirect;
    }

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'name' => 'string',
      'rif' => 'string',
      'address' => 'string',
      'phone' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if (
      $business->name === $validated['name']
      && $business->rif === $validated['rif']
      && $business->address === $validated['address']
      && $business->phone === $validated['phone']
    ) {
      Flash::set(['Los datos del negocio no han cambiado'], 'notes');

      goto redirect;
    }

    if ($this->user->businesses->contains('name', $validated['name'])) {
      Flash::set(['Ya existe un negocio con ese nombre'], 'errors');

      goto redirect;
    }

    $business->update($validated);
    Flash::set(['Negocio actualizado'], 'successes');

    redirect:
    Flight::redirect('/negocios');
  }

  #[Override]
  public function destroy(string $id): void
  {
    $business = $this->user->businesses->find($id);

    if (!$business) {
      Flash::set(['Negocio no encontrado'], 'errors');

      goto redirect;
    }

    $business->delete();
    Flash::set(['Negocio eliminado'], 'notes');

    redirect:
    Flight::redirect('/negocios');
  }
}
