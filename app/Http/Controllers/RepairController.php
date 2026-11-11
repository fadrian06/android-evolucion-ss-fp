<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Flash;
use App\FlashKey;
use App\Models\Business;
use App\Models\Repair;
use App\Models\User;
use App\Services\DailyExchangeRate;
use App\Support\PaymentDetails;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Flight;
use Illuminate\Database\Capsule\Manager;
use InvalidArgumentException;
use Leaf\Form;
use Override;

final readonly class RepairController implements ResourceController
{
  public function __construct(
    private User $user,
    private Business $business,
    private DailyExchangeRate $dailyExchangeRate,
    private Form $form,
  ) {}

  #[Override]
  public function index(): void
  {
    Flight::render(
      'repairs',
      [
        'repairs' => $this->business->repairs,
        'clients' => $this->user->clients,
        'receiptId' => Flash::retrieve(FlashKey::RepairReceipt),
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
    $data = Flight::request()->data->getData();
    $validated = $this->form->validate($data, [
      'client_id' => 'number',
      'description' => 'string',
    ]);

    if (!$validated) {
      Flash::set($this->form->errors(), FlashKey::Errors);
      Flight::redirect('/reparaciones');

      return;
    }

    $rawPrice = trim((string) ($data['price'] ?? ''));

    if ($rawPrice !== '' && (!is_numeric($rawPrice) || (float) $rawPrice < 0)) {
      Flash::set(
        ['El costo de mano de obra debe ser mayor o igual a cero'],
        FlashKey::Errors,
      );

      Flight::redirect('/reparaciones');

      return;
    }

    $price = $rawPrice !== '' ? (float) $rawPrice : 0;

    if (!$this->user->clients->find($validated['client_id'])) {
      Flash::set(['Cliente no encontrado'], FlashKey::Errors);
      Flight::redirect('/reparaciones');

      return;
    }

    $customRate = $this->dailyExchangeRate->customRateFor($this->user);

    if (!$customRate) {
      Flash::set(
        ['Debes establecer una cotización personalizada antes de registrar una reparación'],
        FlashKey::Errors,
      );

      Flight::redirect('/reparaciones');

      return;
    }

    try {
      $payments = PaymentDetails::fromInputs(
        $data['amount'] ?? null,
        $data['method'] ?? null,
        $customRate->rate,
      );
    } catch (InvalidArgumentException $exception) {
      Flash::set([$exception->getMessage()], FlashKey::Errors);
      Flight::redirect('/reparaciones');

      return;
    }


    $repair = Manager::connection()->transaction(function () use (
      $validated,
      $price,
      $customRate,
      $payments,
      $data,
    ): Repair {
      $createdAt = trim((string) ($data['created_at'] ?? ''));

      $referenceDate = $createdAt !== ''
        ? new DateTimeImmutable($createdAt, new DateTimeZone('America/Caracas'))
        : new DateTimeImmutable('today', new DateTimeZone('America/Caracas'));

      $attributes = [
        'client_id' => $validated['client_id'],
        'description' => $validated['description'],
        'price' => $price,
        'price_ves' => round($price * (float) $customRate->rate, 2),
        'due_date' => $referenceDate
          ->add(new DateInterval('P15D'))
          ->format('Y-m-d'),
      ];

      if ($createdAt !== '') {
        $attributes['created_at'] = $createdAt;
      }

      $repair = $this->business->repairs()->make($attributes);

      if ($createdAt !== '') {
        $repair->created_at = $createdAt;
        $repair->updated_at = $createdAt;
      }

      $repair->save();

      $repair
        ->payments()
        ->createMany(PaymentDetails::forPersistence($payments));

      return $repair;
    });

    Flash::set($repair->id, FlashKey::RepairReceipt);
    Flash::set(['Reparación registrada'], FlashKey::Successes);
    Flight::redirect('/reparaciones');
  }

  #[Override]
  public function show(string $id): void
  {
    $repair = $this->business->repairs->find($id);

    if (!$repair instanceof Repair) {
      Flash::set(['Reparación no encontrada'], FlashKey::Errors);
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
