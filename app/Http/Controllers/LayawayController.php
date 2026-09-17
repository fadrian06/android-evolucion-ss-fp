<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Business;
use App\Models\Layaway;
use App\Models\Product;
use App\Models\User;
use App\Services\DailyExchangeRate;
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
        fn(Product $product): bool => (
          $product->category === 'phone'
          && $product->batches->contains(
            fn(Batch $batch): bool => $batch->business_id === $this->business->id && $batch->stock > 0
          )
        )
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
      'imei1' => 'string',
      'imei2' => 'string',
    ]);

    if (!$validated || trim($validated['imei1']) === '' || trim($validated['imei2']) === '') {
      Flash::set(
        $validated ? ['Debes indicar IMEI 1 e IMEI 2'] : $this->form->errors(),
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

    if (!$product || $product->category !== 'phone') {
      Flash::set(['El producto debe ser un teléfono'], 'errors');

      goto redirect;
    }

    $customRate = $this->dailyExchangeRate->customRateFor($this->user);

    if (!$customRate) {
      Flash::set(['Debes establecer una cotización personalizada antes de apartar un teléfono'], 'errors');

      goto redirect;
    }

    $batch = Batch::query()
      ->where('product_id', $product->id)
      ->where('business_id', $this->business->id)
      ->first();

    if (!$batch instanceof Batch || $batch->stock < 1) {
      Flash::set(['No hay existencias de este teléfono en el local seleccionado'], 'errors');

      goto redirect;
    }

    Manager::connection()->transaction(function () use ($batch, $client, $product, $customRate, $validated): void {
      $batch->stock--;
      $batch->save();

      $this->business->layaways()->create([
        'client_id' => $client->id,
        'product_id' => $product->id,
        'price' => $product->price,
        'price_ves' => round($product->price * (float) $customRate->rate, 2),
        'imei1' => trim($validated['imei1']),
        'imei2' => trim($validated['imei2']),
      ]);
    });

    Flash::set(['Teléfono apartado'], 'successes');

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
