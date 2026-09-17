<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class ProductController implements ResourceController
{
  public function __construct(private User $user, private Form $form)
  {
    //
  }

  #[Override]
  public function index(): void
  {
    Flight::render('products', [
      'products' => $this->user->products,
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
      'price' => 'number',
      'stocks' => 'array<number>',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($this->user->products->contains('name', $validated['name'])) {
      Flash::set(['Ya existe un producto con ese nombre'], 'errors');

      goto redirect;
    }

    $batches = [];

    foreach ($validated['stocks'] as $businessId => $stock) {
      $business = $this->user->businesses->find($businessId);

      if (!$business) {
        Flash::set(['Negocio no encontrado'], 'errors');

        goto redirect;
      }

      if ($stock < 0) {
        Flash::set(['El stock no puede ser negativo'], 'errors');

        goto redirect;
      }

      $batches[] = ['business_id' => $businessId, 'stock' => $stock];
    }

    $product = $this->user->products()->create([
      'name' => $validated['name'],
      'price' => $validated['price'],
    ]);

    if ($product instanceof Product) {
      $product->batches()->createMany($batches);
    }

    Flash::set(['Producto registrado'], 'successes');

    redirect:
    Flight::redirect('/productos');
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
    $product = $this->user->products->find($id);

    if (!$product) {
      Flash::set(['Producto no encontrado'], 'errors');

      goto redirect;
    }

    $validated = $this->form->validate(Flight::request()->data["$product->id"], [
      'name' => 'string',
      'price' => 'number',
      'stocks' => 'array<number>',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    if ($this->user->products->first(
      static fn(Product $p): bool => $p->name === $validated['name'] && $p->id !== $product->id
    )) {
      Flash::set(['Ya existe un producto con ese nombre'], 'errors');

      goto redirect;
    }

    $product->batches->each(static fn(Batch $batch) => $batch->delete());

    $batches = [];

    foreach ($validated['stocks'] as $businessId => $stock) {
      $business = $this->user->businesses->find($businessId);

      if (!$business) {
        Flash::set(['Negocio no encontrado'], 'errors');

        goto redirect;
      }

      if ($stock < 0) {
        Flash::set(['El stock no puede ser negativo'], 'errors');

        goto redirect;
      }

      $batches[] = ['business_id' => $businessId, 'stock' => $stock];
    }

    $product->update([
      'name' => $validated['name'],
      'price' => $validated['price'],
    ]);

    if ($product instanceof Product) {
      $product->batches()->createMany($batches);
    }

    Flash::set(['Producto actualizado'], 'successes');

    redirect:
    Flight::redirect('/productos');
  }

  #[Override]
  public function destroy(string $id): void
  {
    $product = $this->user->products->find($id);

    if (!$product) {
      Flash::set(['Producto no encontrado'], 'errors');

      goto redirect;
    }

    $product->delete();
    Flash::set(['Producto eliminado'], 'notes');

    redirect:
    Flight::redirect('/productos');
  }
}
