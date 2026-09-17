<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $business_id
 * @property-read int $price
 * @property-read string $price_ves
 * @property-read int $quantity
 * @property-read null|string $imei1
 * @property-read null|string $imei2
 * @property-read null|string $code
 * @property-read Product $product
 * @property-read Business $business
 */
final class Item extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = [
    'product_id',
    'business_id',
    'price',
    'price_ves',
    'quantity',
    'imei1',
    'imei2',
    'code',
  ];

  /** @return BelongsTo<Business, $this> */
  public function business(): BelongsTo
  {
    return $this->belongsTo(Business::class);
  }

  /** @return BelongsTo<Product, $this> */
  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  public function getTotal(): int
  {
    return $this->price * $this->quantity;
  }

  public function getTotalVes(): float
  {
    return (float) $this->price_ves * $this->quantity;
  }
}
