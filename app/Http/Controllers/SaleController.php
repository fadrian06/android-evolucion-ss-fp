<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class SaleController implements ResourceController
{
  public function __construct(
    private User $user,
    private Business $business,
    private Form $form,
  ) {
    //
  }

  #[Override]
  public function index(): void
  {
    Flight::render('sales', [
      'sales' => $this->business->sales,
      'clients' => $this->user->clients,
      'products' => $this
        ->user
        ->products
        ->filter(static fn(Product $product): bool => $product->getStock() > 0)
        ->load('batches'),
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
      'client_id' => "number",
      'product_id' => "array<number>",
      'business_id' => "array<number>",
      'quantity' => 'array<number>',
      'amount' => 'array<number>',
      'method' => 'array<string>',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    $items = [];
    $payments = [];

    foreach ($validated['product_id'] as $index => $productId) {
      $businessId = $validated['business_id'][$index];
      $quantity = $validated['quantity'][$index];

      $product = $this->user->products->find($productId);
      $business = $this->user->businesses->find($businessId);

      $batch = $product
        ->batches
        ->first(static fn(Batch $batch): bool => $batch->business->id == $businessId);

      if (!$business) {
        Flash::set(['Negocio no encontrado'], 'errors');

        goto redirect;
      }

      if (!$product) {
        Flash::set(['Producto no encontrado'], 'errors');

        goto redirect;
      }

      if (!$batch) {
        Flash::set(['Lote no encontrado'], 'errors');

        goto redirect;
      }

      if ($quantity > $batch->stock) {
        $unit = $batch->stock === 1 ? 'unidad' : 'unidades';
        $verb = $batch->stock === 1 ? 'queda' : 'quedan';

        $message = $batch->stock
          ? "Solo $verb $batch->stock $unit $product->name en $business->name"
          : "No quedan unidades de $product->name en $business->name";

        Flash::set([$message], 'errors');

        goto redirect;
      }

      $items[] = [
        'product_id' => $product->id,
        'price' => $product->price,
        'quantity' => $quantity,
      ];

      $batch->stock -= $quantity;
      $batch->save();
    }

    foreach ($validated['amount'] as $index => $amount) {
      $method = $validated['method'][$index];

      $payments[] = [
        'amount' => $amount,
        'method' => $method,
      ];
    }

    $sale = $this->business->sales()->create(['client_id' => $validated['client_id']]);

    if ($sale instanceof Sale) {
      $sale->items()->createMany($items);
      $sale->payments()->createMany($payments);

      if ($sale->getRemainingAmount() > 0) {
        Flash::set(['El pago no cubre el total de la venta'], 'warning');
      }

      if ($sale->getTotalPaid() > $sale->getTotal()) {
        Flash::set(['El pago excede el total de la venta'], 'notes');
      }
    }

    Flash::set(['Venta registrada'], 'successes');

    redirect:
    Flight::redirect('/ventas');
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
    throw new \Exception('Not implemented');
  }

  #[Override]
  public function destroy(string $id): void
  {
    throw new \Exception('Not implemented');
  }
}
