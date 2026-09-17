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
 * @property-read 'phone'|'accessory' $category
 * @property-read int $price
 * @property-read Collection<int, Batch> $batches
 */
final class Product extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['name', 'category', 'price'];

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
