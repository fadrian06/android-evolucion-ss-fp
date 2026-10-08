<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Layaway;
use App\Models\Product;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use Flight;
use Illuminate\Database\Capsule\Manager;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class LayawayController implements ResourceController
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
    $products = $this->user->products
      ->load('batches')
      ->filter(
        fn(Product $product): bool => $product->batches->contains(
            fn(Batch $batch): bool => $batch->business_id === $this->business->id && $batch->stock > 0
          ),
      );

    Flight::render('layaways', [
      'layaways' => $this->business->layaways,
      'clients' => $this->user->clients,
      'products' => $products,
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
      'client_id' => 'number',
      'product_id' => 'number',
    ]);

    if (!$validated) {
      Flash::set(
        $this->form->errors(),
        'errors',
      );

      goto redirect;
    }

    $client = $this->user->clients->find($validated['client_id']);
    $product = $this->user->products->find($validated['product_id']);

    if (!$client) {
      Flash::set(['Cliente no encontrado'], 'errors');

      goto redirect;
    }

    if (!$product) {
      Flash::set(['Producto no encontrado'], 'errors');

      goto redirect;
    }

    $data = Flight::request()->data->getData();
    $imei1 = trim((string) ($data['imei1'] ?? ''));
    $imei2 = trim((string) ($data['imei2'] ?? ''));
    $code = trim((string) ($data['code'] ?? ''));

    if ($product->category === 'phone' && ($imei1 === '' || $imei2 === '')) {
      Flash::set(['Debes indicar IMEI 1 e IMEI 2'], 'errors');

      goto redirect;
    }

    $customRate = $this->dailyExchangeRate->customRateFor($this->user);

    if (!$customRate) {
      Flash::set(['Debes establecer una cotización personalizada antes de crear un apartado'], 'errors');

      goto redirect;
    }

    $batch = Batch::query()
      ->where('product_id', $product->id)
      ->where('business_id', $this->business->id)
      ->first();

    if (!$batch instanceof Batch || $batch->stock < 1) {
      Flash::set(['No hay existencias de este producto en el local seleccionado'], 'errors');

      goto redirect;
    }

    try {
      $payments = PaymentDetails::fromInputs(
        $data['amount'] ?? null,
        $data['method'] ?? null,
        (float) $customRate->rate,
      );
    } catch (\InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], 'errors');

      goto redirect;
    }

    if (PaymentDetails::totalUsd($payments) > (float) $product->price) {
      Flash::set(['Los pagos iniciales exceden el saldo del apartado'], 'errors');

      goto redirect;
    }

    Manager::connection()->transaction(function () use ($batch, $client, $product, $customRate, $imei1, $imei2, $code, $payments): void {
      $batch->stock--;
      $batch->save();

      $layaway = $this->business->layaways()->create([
        'client_id' => $client->id,
        'product_id' => $product->id,
        'price' => $product->price,
        'price_ves' => round($product->price * (float) $customRate->rate, 2),
        'imei1' => $product->category === 'phone' ? $imei1 : null,
        'imei2' => $product->category === 'phone' ? $imei2 : null,
        'code' => $product->category === 'accessory' && $code !== '' ? $code : null,
      ]);
      $layaway->payments()->createMany(PaymentDetails::forPersistence($payments));
    });

    Flash::set(['Producto apartado'], 'successes');

    redirect:
    Flight::redirect('/apartados');
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
