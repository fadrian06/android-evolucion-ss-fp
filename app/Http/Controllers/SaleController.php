<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\DailyExchangeRate;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class SaleController implements ResourceController
{
  public function __construct(
    private User $user,
    private Business $business,
    private DailyExchangeRate $dailyExchangeRate,
    private Form $form,
  ) {
    //
  }

  #[Override]
  public function index(): void
  {
    $invoiceId = Flash::displaySaved();
    Flash::clearSaved();

    Flight::render('sales', [
      'sales' => $this->business->sales,
      'clients' => $this->user->clients,
      'products' => $this
        ->user
        ->products
        ->filter(static fn(Product $product): bool => $product->getStock() > 0)
        ->load('batches'),
      'businesses' => $this->user->businesses,
      'invoiceId' => $invoiceId,
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
    $data = Flight::request()->data->getData();
    $validated = $this->form->validate($data, [
      'client_id' => "number",
      'product_id' => "array<number>",
      'business_id' => "array<number>",
      'quantity' => 'array<number>',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), 'errors');

      goto redirect;
    }

    $customRate = $this->dailyExchangeRate->customRateFor($this->user);

    if (!$customRate) {
      Flash::set(['Debes establecer una cotización personalizada antes de facturar'], 'errors');

      goto redirect;
    }

    $exchangeRate = (float) $customRate->rate;

    $phoneItems = [];
    $accessoryItems = [];
    $stockChanges = [];
    $hasPhone = false;

    foreach ($validated['product_id'] as $index => $productId) {
      $businessId = $validated['business_id'][$index] ?? null;
      $quantity = $validated['quantity'][$index] ?? null;

      if ($businessId === null || $quantity === null) {
        Flash::set(['Los datos de los productos no coinciden'], 'errors');

        goto redirect;
      }

      $product = $this->user->products->find($productId);
      $business = $this->user->businesses->find($businessId);

      if (!$product) {
        Flash::set(['Producto no encontrado'], 'errors');

        goto redirect;
      }

      if (!$business) {
        Flash::set(['Negocio no encontrado'], 'errors');

        goto redirect;
      }

      $batch = $product
        ->batches
        ->first(static fn(Batch $batch): bool => $batch->business->id == $businessId);

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

      $item = [
        'product_id' => $product->id,
        'price' => $product->price,
        'price_ves' => round($product->price * $exchangeRate, 2),
        'quantity' => $quantity,
      ];

      if ($product->category === 'phone') {
        $imei1 = $data['imei1'][$index] ?? null;
        $imei2 = $data['imei2'][$index] ?? null;

        if (
          (int) $quantity !== 1
          || !is_string($imei1)
          || trim($imei1) === ''
          || !is_string($imei2)
          || trim($imei2) === ''
        ) {
          Flash::set(['Cada teléfono debe tener cantidad 1, IMEI 1 e IMEI 2'], 'errors');

          goto redirect;
        }

        $hasPhone = true;
        $phoneItems[] = [...$item, 'imei1' => trim($imei1), 'imei2' => trim($imei2)];
      } else {
        $code = $data['code'][$index] ?? null;

        if (!is_string($code) || trim($code) === '') {
          Flash::set(['Cada accesorio debe tener un código'], 'errors');

          goto redirect;
        }

        $accessoryItems[] = [...$item, 'code' => trim($code)];
      }

      $stockChanges[] = ['batch' => $batch, 'quantity' => $quantity];
    }

    $payments = [];

    if ($hasPhone && (isset($data['amount']) || isset($data['method']))) {
      Flash::set(['Las ventas con teléfonos no aceptan pagos iniciales'], 'errors');

      goto redirect;
    }

    if (!$hasPhone) {
      $paymentValidation = $this->form->validate($data, [
        'amount' => 'array<number>',
        'method' => 'array<string>',
      ]);

      if (!$paymentValidation) {
        Flash::set($this->form->errors(), 'errors');

        goto redirect;
      }

      foreach ($paymentValidation['amount'] as $index => $amount) {
        $method = $paymentValidation['method'][$index] ?? null;

        if (!is_string($method)) {
          Flash::set(['Los datos de los pagos no coinciden'], 'errors');

          goto redirect;
        }

        $payments[] = ['amount' => $amount, 'method' => $method];
      }
    }

    $invoiceIds = Manager::connection()->transaction(function () use (
      $validated,
      $stockChanges,
      $phoneItems,
      $accessoryItems,
      $payments,
    ): array {
      foreach ($stockChanges as ['batch' => $batch, 'quantity' => $quantity]) {
        $batch->stock -= $quantity;
        $batch->save();
      }

      $invoiceIds = [];

      foreach ($phoneItems as $item) {
        $sale = $this->business->sales()->create(['client_id' => $validated['client_id']]);
        $sale->items()->create($item);
        $invoiceIds[] = $sale->id;
      }

      if ($accessoryItems) {
        $sale = $this->business->sales()->create(['client_id' => $validated['client_id']]);
        $sale->items()->createMany($accessoryItems);
        $sale->payments()->createMany($payments);
        $invoiceIds[] = $sale->id;
      }

      return $invoiceIds;
    });

    Flash::save(implode(',', $invoiceIds));
    Flash::set(['Venta registrada'], 'successes');

    redirect:
    Flight::redirect('/ventas');
  }

  #[Override]
  public function show(string $id): void
  {
    $invoice = null;

    foreach ($this->user->businesses as $business) {
      $invoice = $business->sales->find($id);

      if ($invoice instanceof Sale) {
        break;
      }
    }

    if (!$invoice instanceof Sale) {
      Flash::set(['Factura no encontrada'], 'errors');
      Flight::redirect('/ventas');

      return;
    }

    Flight::render('invoice', ['invoice' => $invoice], 'slot');
    Flight::render('components/layout');
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
