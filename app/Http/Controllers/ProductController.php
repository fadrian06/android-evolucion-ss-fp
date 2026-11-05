<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\Product;
use App\Models\User;
use App\Services\DailyExchangeRate;
use Flight;
use Leaf\Form;
use NoDiscard;
use Override;

final readonly class ProductController implements ResourceController
{
  public function __construct(
    private User $user,
    private DailyExchangeRate $dailyExchangeRate,
    private Form $form,
  ) {}

  #[Override]
  public function index(): void
  {
    Flight::render(
      'products',
      [
        'products' => $this->user->products,
        'businesses' => $this->user->businesses,
        'customRate' => $this->dailyExchangeRate->customRateFor($this->user),
      ],
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
    $this->ensureNameIsNotDuplicated($validated['name']);

    $batches = [];

    foreach ($validated['stocks'] as $businessId => $stock) {
      $batches[] = ['business_id' => $businessId, 'stock' => $stock];
    }

    $product = $this->user
      ->products()
      ->create([
        'name' => $validated['name'],
        'category' => $validated['category'],
        'price' => $validated['price'],
      ]);

    $product->batches()->createMany($batches);
    Flash::set(['Producto registrado'], FlashKey::Successes);
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
    $product = $this->getProductById($id);
    $validated = $this->getValidated();

    if ($product->name !== $validated['name']) {
      $this->ensureNameIsNotDuplicated($validated['name']);
    }

    $batches = [];

    foreach ($validated['stocks'] as $businessId => $stock) {
      $batches[] = ['business_id' => $businessId, 'stock' => $stock];
    }

    $product->batches()->delete();

    $product->update([
      'name' => $validated['name'],
      'category' => $validated['category'],
      'price' => $validated['price'],
    ]);

    $product->batches()->createMany($batches);
    Flash::set(['Producto actualizado'], FlashKey::Successes);
    Flight::redirect('/productos');
  }

  #[Override]
  public function destroy(string $id): void
  {
    $product = $this->getProductById($id);

    if (!$product->canBeDeleted()) {
      Flash::set(
        ['No se puede eliminar un producto que tiene registros asociados'],
        FlashKey::Errors,
      );

      Flight::redirect('/productos');

      return;
    }

    $product->batches()->delete();
    $product->delete();
    Flash::set(['Producto eliminado'], FlashKey::Notes);
    Flight::redirect('/productos');
  }

  #[NoDiscard]
  private function getProductById(string $id): Product
  {
    $product = $this->user->products->find($id);

    if (!$product) {
      Flash::set(['Producto no encontrado'], FlashKey::Errors);
      Flight::redirect('/productos');

      exit;
    }

    return $product;
  }

  /**
   * @return array{
   *   name: string,
   *   category: 'phone'|'accessory'|'spare_part',
   *   price: float,
   *   stocks: array<int, int>,
   * }
   */
  #[NoDiscard]
  private function getValidated(): array
  {
    $rules = [
      'name' => 'string',
      'category' => 'in:[phone,accessory,spare_part]',
      'price' => 'min:0.01',
    ];

    foreach ($this->user->businesses->pluck('id') as $id) {
      $rules["stocks.$id"] = 'number';
    }

    $validated = $this->form->validate(
      Flight::request()->data->getData(),
      $rules,
    );

    if (!$validated) {
      Flash::set($this->form->errors(), FlashKey::Errors);
      Flight::redirect('/productos');

      exit;
    }

    return $validated;
  }

  private function ensureNameIsNotDuplicated(string $name): void
  {
    if ($this->user->products()->where('name', $name)->exists()) {
      Flash::set(['Ya existe un producto con ese nombre'], FlashKey::Errors);
      Flight::redirect('/productos');

      exit;
    }
  }
}
