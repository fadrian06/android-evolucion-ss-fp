<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read DateTimeInterface $created_at
 * @property-read ?DateTimeInterface $cancelled_at
 * @property-read Business $business
 * @property-read Client $client
 * @property-read Collection<int, Item> $items
 * @mixin Builder<static>
 */
final class Sale extends Model
{
  protected $fillable = ['client_id'];
  protected $casts = ['cancelled_at' => 'datetime'];

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

  public function getRemainingAmount(): float
  {
    return round($this->getTotal() - $this->getTotalPaid(), 2);
  }

  public function getTotal(): float
  {
    $total = 0.0;

    foreach ($this->items as $item) {
      $total += $item->getTotal();
    }

    return round($total, 2);
  }

  public function getTotalVes(): float
  {
    $total = 0;

    foreach ($this->items as $item) {
      $total += $item->getTotalVes();
    }

    return $total;
  }

  public function getTotalPaid(): float
  {
    $total = 0;

    foreach ($this->payments()->lazyById(1000, 'id') as $payment) {
      $total += $payment->getAmountUsd();
    }

    return round($total, 2);
  }

  public function hasPayments(): bool
  {
    return $this->getTotalPaid() > 0;
  }

  public function isFullyPaid(): bool
  {
    return !$this->getRemainingAmount();
  }

  public function isCancelled(): bool
  {
    return $this->cancelled_at !== null;
  }

  public function canBePaid(): bool
  {
    return !$this->isFullyPaid() && !$this->isCancelled();
  }
}
