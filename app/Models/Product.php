<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read 'phone'|'accessory'|'spare_part' $category
 * @property-read float $price
 * @property-read Collection<int, Batch> $batches
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

  public function getStock(): int
  {
    $stock = 0;

    foreach ($this->batches as $batch) {
      $stock += $batch->stock;
    }

    return $stock;
  }
}
