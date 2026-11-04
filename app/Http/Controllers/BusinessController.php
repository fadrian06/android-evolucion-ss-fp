<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\Business;
use App\Models\User;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Leaf\Form;
use NoDiscard;
use Override;

final readonly class BusinessController implements ResourceController
{
  public function __construct(
    private User $user,
    private Form $form,
  ) {}

  #[Override]
  public function index(): void
  {
    Flight::render(
      'businesses',
      ['businesses' => $this->user->businesses],
      'slot',
    );

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
    $validated = $this->getValidated();

    if ($this->user->businesses()->where(
      'name',
      $validated['name'],
    )->exists()) {
      Flash::set(['Ya existe un negocio con ese nombre'], FlashKey::Errors);
      Flight::redirect('/negocios');

      return;
    }

    if ($this->user->businesses()->where(
      'address',
      $validated['address'],
    )->exists()) {
      Flash::set(['Ya existe un negocio con esa dirección'], FlashKey::Errors);
      Flight::redirect('/negocios');

      return;
    }

    $this->user->businesses()->create($validated);
    Flash::set(['Negocio registrado'], FlashKey::Successes);
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
    $business = $this->getBusinessById($id);
    $validated = $this->getValidated();

    if ($this->user
      ->businesses()
      ->where('name', $validated['name'])
      ->where('id', '!=', $business->id)
      ->exists()) {
      Flash::set(['Ya existe un negocio con ese nombre'], FlashKey::Errors);
      Flight::redirect('/negocios');

      return;
    }

    if ($this->user
      ->businesses()
      ->where('address', $validated['address'])
      ->where('id', '!=', $business->id)
      ->exists()) {
      Flash::set(['Ya existe un negocio con esa dirección'], FlashKey::Errors);
      Flight::redirect('/negocios');

      return;
    }

    Manager::connection()->transaction(static function () use (
      $business,
      $validated,
    ): void {
      $business->refreshForUpdate()->update($validated);
    });

    Flash::set(['Negocio actualizado'], FlashKey::Successes);
    Flight::redirect('/negocios');
  }

  #[Override]
  public function destroy(string $id): void
  {
    $business = $this->getBusinessById($id);

    if (!$business->canBeDeleted()) {
      Flash::set(
        ['No se puede eliminar un negocio que tiene registros asociados'],
        FlashKey::Errors,
      );

      Flight::redirect('/negocios');

      return;
    }

    $business->delete();
    Flash::set(['Negocio eliminado'], FlashKey::Notes);
    Flight::redirect('/negocios');
  }

  #[NoDiscard]
  private function getBusinessById(string $id): Business
  {
    $business = $this->user->businesses->find($id);

    if (!$business) {
      Flash::set(['Negocio no encontrado'], FlashKey::Errors);
      Flight::redirect('/negocios');

      exit;
    }

    return $business;
  }

  private function getValidated(): array
  {
    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'name' => 'string',
      'rif' => 'string',
      'address' => 'string',
      'phone' => 'string',
    ]);

    if (!$validated) {
      Flash::set(
        array_filter($this->form->errors(), 'is_string'),
        FlashKey::Errors,
      );

      Flight::redirect('/negocios');

      exit;
    }

    return $validated;
  }
}
