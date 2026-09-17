<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property-read int $id
 * @property-read string $description
 * @property-read int $price
 * @property-read string $price_ves
 * @property-read DateTimeInterface $due_date
 * @property-read Business $business
 * @property-read Client $client
 * @property-read Collection<int, RepairPayment> $payments
 */
final class Repair extends Model
{
  #[Override]
  protected $fillable = ['client_id', 'description', 'price', 'price_ves', 'due_date'];

  #[Override]
  protected function casts(): array
  {
    return ['due_date' => 'date'];
  }

  /** @return BelongsTo<Business, $this> */
  public function business(): BelongsTo
  {
    return $this->belongsTo(Business::class);
  }

  /** @return BelongsTo<Client, $this> */
  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }

  /** @return HasMany<RepairPayment, $this> */
  public function payments(): HasMany
  {
    return $this->hasMany(RepairPayment::class);
  }

  public function getTotalPaidVes(): float
  {
    $total = 0;

    foreach ($this->payments as $payment) {
      $total += (float) $payment->amount_ves;
    }

    return $total;
  }

  public function getRemainingAmountVes(): float
  {
    return (float) $this->price_ves - $this->getTotalPaidVes();
  }
}
