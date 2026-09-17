<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $price
 * @property-read int $quantity
 * @property-read null|string $imei1
 * @property-read null|string $imei2
 * @property-read null|string $code
 * @property-read Product $product
 */
final class Item extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['product_id', 'price', 'quantity', 'imei1', 'imei2', 'code'];

  /** @return BelongsTo<Product, $this> */
  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  public function getTotal(): int
  {
    return $this->price * $this->quantity;
  }
}
