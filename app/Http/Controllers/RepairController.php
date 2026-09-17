<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Repair;
use App\Models\User;
use App\Services\DailyExchangeRate;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Flight;
use Leaf\Flash;
use Leaf\Form;
use Override;

final readonly class RepairController implements ResourceController
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
    Flight::render('repairs', [
      'repairs' => $this->business->repairs,
      'clients' => $this->user->clients,
      'receiptId' => Flash::display('repairReceipt'),
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
      'description' => 'string',
      'price' => 'number',
    ]);

    if (!$validated || (int) $validated['price'] <= 0) {
      Flash::set(
        $validated ? ['El precio debe ser mayor que cero'] : $this->form->errors(),
        'errors',
      );
      Flight::redirect('/reparaciones');

      return;
    }

    if (!$this->user->clients->find($validated['client_id'])) {
      Flash::set(['Cliente no encontrado'], 'errors');
      Flight::redirect('/reparaciones');

      return;
    }

    $customRate = $this->dailyExchangeRate->customRateFor($this->user);

    if (!$customRate) {
      Flash::set(['Debes establecer una cotización personalizada antes de registrar una reparación'], 'errors');
      Flight::redirect('/reparaciones');

      return;
    }

    $repair = $this->business->repairs()->create([
      'client_id' => $validated['client_id'],
      'description' => $validated['description'],
      'price' => $validated['price'],
      'price_ves' => round((int) $validated['price'] * (float) $customRate->rate, 2),
      'due_date' => (new DateTimeImmutable('today', new DateTimeZone('America/Caracas')))
        ->add(new DateInterval('P15D'))
        ->format('Y-m-d'),
    ]);

    Flash::set((string) $repair->id, 'repairReceipt');
    Flash::set(['Reparación registrada'], 'successes');
    Flight::redirect('/reparaciones');
  }

  #[Override]
  public function show(string $id): void
  {
    $repair = $this->business->repairs->find($id);

    if (!$repair instanceof Repair) {
      Flash::set(['Reparación no encontrada'], 'errors');
      Flight::redirect('/reparaciones');

      return;
    }

    Flight::render('repair-receipt', ['repair' => $repair], 'slot');
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
