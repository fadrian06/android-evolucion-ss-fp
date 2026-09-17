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
 * @property-read DateTimeInterface $created_at
 * @property-read DateTimeInterface $updated_at
 * @property-read null|DateTimeInterface $cancelled_at
 * @property-read Business $business
 * @property-read Client $client
 * @property-read Collection<int, Item> $items
 * @property-read Collection<int, Payment> $payments
 */
final class Sale extends Model
{
  protected $fillable = ['client_id'];

  #[Override]
  protected function casts(): array
  {
    return ['cancelled_at' => 'datetime'];
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

  /** @return HasMany<Item, $this> */
  public function items(): HasMany
  {
    return $this->hasMany(Item::class);
  }

  /** @return HasMany<Payment, $this> */
  public function payments(): HasMany
  {
    return $this->hasMany(Payment::class);
  }

  public function getRemainingAmount(): int
  {
    return $this->getTotal() - $this->getTotalPaid();
  }

  public function getTotal(): int
  {
    $total = 0;

    foreach ($this->items as $item) {
      $total += $item->getTotal();
    }

    return $total;
  }

  public function getTotalVes(): float
  {
    $total = 0;

    foreach ($this->items as $item) {
      $total += $item->getTotalVes();
    }

    return $total;
  }

  public function getTotalPaid(): int
  {
    $total = 0;

    foreach ($this->payments as $payment) {
      $total += $payment->amount;
    }

    return $total;
  }
}
