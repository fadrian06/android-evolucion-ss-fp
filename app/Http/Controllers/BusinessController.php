<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Item;
use App\Models\User;
use Flight;
use Illuminate\Database\UniqueConstraintViolationException;
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
      'businesses' => $this->user->businesses()
        ->withCount(['batches', 'items', 'sales', 'repairs', 'layaways'])
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
      'rif' => 'string',
      'address' => 'string',
      'phone' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($this->user->businesses()->where('name', $validated['name'])->exists()) {
      Flash::set(['Ya existe un negocio con ese nombre'], 'errors');

      goto redirect;
    }

    if (Business::query()->where('rif', $validated['rif'])->exists()) {
      Flash::set(['Ya existe un negocio con ese RIF'], 'errors');

      goto redirect;
    }

    if ($this->user->businesses()->where('address', $validated['address'])->exists()) {
      Flash::set(['Ya existe un negocio con esa dirección'], 'errors');

      goto redirect;
    }

    try {
      $this->user->businesses()->create($validated);
    } catch (UniqueConstraintViolationException $exception) {
      error_log($exception->getMessage());
      Flash::set(['No se pudo registrar el negocio porque ya existe un dato único'], 'errors');

      goto redirect;
    }

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

    if ($this->user->businesses()
      ->where('name', $validated['name'])
      ->where('id', '!=', $business->id)
      ->exists()) {
      Flash::set(['Ya existe un negocio con ese nombre'], 'errors');

      goto redirect;
    }

    if (Business::query()
      ->where('rif', $validated['rif'])
      ->where('id', '!=', $business->id)
      ->exists()) {
      Flash::set(['Ya existe un negocio con ese RIF'], 'errors');

      goto redirect;
    }

    if ($this->user->businesses()
      ->where('address', $validated['address'])
      ->where('id', '!=', $business->id)
      ->exists()) {
      Flash::set(['Ya existe un negocio con esa dirección'], 'errors');

      goto redirect;
    }

    try {
      $business->update($validated);
    } catch (UniqueConstraintViolationException $exception) {
      error_log($exception->getMessage());
      Flash::set(['No se pudo actualizar el negocio porque ya existe un dato único'], 'errors');

      goto redirect;
    }

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

    if (
      Batch::query()->where('business_id', $business->id)->exists()
      || Item::query()->where('business_id', $business->id)->exists()
      || $business->sales()->exists()
      || $business->repairs()->exists()
      || $business->layaways()->exists()
    ) {
      Flash::set(['No se puede eliminar un negocio que tiene registros asociados'], 'errors');

      goto redirect;
    }

    $business->delete();
    Flash::set(['Negocio eliminado'], 'notes');

    redirect:
    Flight::redirect('/negocios');
  }
}
