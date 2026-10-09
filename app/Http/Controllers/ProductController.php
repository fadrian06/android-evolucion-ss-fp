<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\UniqueConstraintViolationException;
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
      'products' => $this->user->products()
        ->with('batches')
        ->withCount([
          'batches as stocked_batches_count' => static fn($query) => $query->where('stock', '>', 0),
          'items',
          'layaways',
        ])
        ->get(),
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
      'category' => 'in:[phone,accessory,spare_part]',
      'price' => 'optional',
      'stocks' => 'array<number>',
    ]);

    if (!$validated || !is_numeric($validated['price'] ?? null) || (float) $validated['price'] <= 0) {
      Flash::set($this->form->errors(), 'errors');
      if ($validated) {
        Flash::set(['El precio debe ser mayor que cero'], 'errors');
      }

      goto redirect;
    }

    if ($this->user->products()->where('name', $validated['name'])->exists()) {
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

    try {
      Manager::connection()->transaction(function () use ($validated, $batches): void {
        $product = $this->user->products()->create([
          'name' => $validated['name'],
          'category' => $validated['category'],
          'price' => $validated['price'],
        ]);
        $product->batches()->createMany($batches);
      });
    } catch (UniqueConstraintViolationException $exception) {
      error_log($exception->getMessage());
      Flash::set(['No se pudo registrar el producto porque ya existe un dato único'], 'errors');

      goto redirect;
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

    $validated = $this->form->validate(Flight::request()->data->getData(), [
      'name' => 'string',
      'category' => 'in:[phone,accessory,spare_part]',
      'price' => 'optional',
      'stocks' => 'array<number>',
    ]);

    if (!$validated || !is_numeric($validated['price'] ?? null) || (float) $validated['price'] <= 0) {
      Flash::set($this->form->errors(), 'errors');
      if ($validated) {
        Flash::set(['El precio debe ser mayor que cero'], 'errors');
      }

      goto redirect;
    }

    if ($this->user->products->first(
      static fn(Product $p): bool => (
        $p->name === $validated['name']
        && $p->id !== $product->id
      )
    )) {
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

    try {
      Manager::connection()->transaction(function () use ($product, $validated, $batches): void {
        $product->batches()->delete();
        $product->update([
          'name' => $validated['name'],
          'category' => $validated['category'],
          'price' => $validated['price'],
        ]);
        $product->batches()->createMany($batches);
      });
    } catch (UniqueConstraintViolationException $exception) {
      error_log($exception->getMessage());
      Flash::set(['No se pudo actualizar el producto porque ya existe un dato único'], 'errors');

      goto redirect;
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

    if (
      $product->batches()->where('stock', '>', 0)->exists()
      || $product->items()->exists()
      || $product->layaways()->exists()
    ) {
      Flash::set(['No se puede eliminar un producto que tiene registros asociados'], 'errors');

      goto redirect;
    }

    $product->delete();
    Flash::set(['Producto eliminado'], 'notes');

    redirect:
    Flight::redirect('/productos');
  }
}
