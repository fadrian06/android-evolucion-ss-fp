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
 * @property-read int $price
 * @property-read string $price_ves
 * @property-read string $imei1
 * @property-read string $imei2
 * @property-read null|DateTimeInterface $cancelled_at
 * @property-read Client $client
 * @property-read Product $product
 * @property-read Collection<int, LayawayPayment> $payments
 */
final class Layaway extends Model
{
  #[Override]
  protected $fillable = ['client_id', 'product_id', 'price', 'price_ves', 'imei1', 'imei2'];

  #[Override]
  protected function casts(): array
  {
    return ['cancelled_at' => 'datetime'];
  }

  /** @return BelongsTo<Client, $this> */
  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }

  /** @return BelongsTo<Product, $this> */
  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  /** @return HasMany<LayawayPayment, $this> */
  public function payments(): HasMany
  {
    return $this->hasMany(LayawayPayment::class);
  }

  public function getTotalPaid(): int
  {
    $total = 0;

    foreach ($this->payments as $payment) {
      $total += $payment->amount;
    }

    return $total;
  }

  public function getRemainingAmount(): int
  {
    return $this->price - $this->getTotalPaid();
  }
}
