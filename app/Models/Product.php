<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NoDiscard;
use Override;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read 'phone'|'accessory'|'spare_part' $category
 * @property-read float $price
 * @property-read Collection<int, Batch> $batches
 * @property-read Collection<int, Item> $items
 * @property-read Collection<int, Layaway> $layaways
 * @property-read int $items_count
 * @property-read int $layaways_count
 */
final class Product extends Model
{
  public const CATEGORIES = ['phone', 'accessory', 'spare_part'];

  public $timestamps = false;
  protected $fillable = ['name', 'category', 'price'];

  #[Override]
  protected function casts(): array
  {
    return ['price' => 'float'];
  }

  /** @return HasMany<Batch, $this> */
  public function batches(): HasMany
  {
    return $this->hasMany(Batch::class);
  }

  /** @return HasMany<Item, $this> */
  public function items(): HasMany
  {
    return $this->hasMany(Item::class);
  }

  /** @return HasMany<Layaway, $this> */
  public function layaways(): HasMany
  {
    return $this->hasMany(Layaway::class);
  }

  public function getStock(): int
  {
    $stock = 0;

    foreach ($this->batches as $batch) {
      $stock += $batch->stock;
    }

    return $stock;
  }

  #[NoDiscard]
  public function canBeDeleted(): bool
  {
    return !$this->items->count() && !$this->layaways->count();
  }

  public function getBusinessIdsWithStock(): string
  {
    return $this->batches
      ->filter(static fn(Batch $batch) => $batch->stock > 0)
      ->pluck('business_id')
      ->implode(',');
  }

  public function isPhone(): bool
  {
    return $this->category === 'phone';
  }

  #[NoDiscard]
  public function getStockInBusiness(Business $business): int
  {
    $batch = $this->batches->first(
      static fn(Batch $batch) => $batch->business->id === $business->id,
    );

    if ($batch) {
      return $batch->stock;
    }

    return 0;
  }
}
