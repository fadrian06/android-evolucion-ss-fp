<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NoDiscard;

/**
 * @property-read int $quantity
 * @property-read ?string $imei1
 * @property-read ?string $imei2
 * @property-read ?string $code
 * @property-read Product $product
 * @property-read Business $business
 * @mixin Builder<static>
 */
final class Item extends Model
{
  public $timestamps = false;

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

  protected $casts = ['price' => 'float', 'price_ves' => 'float'];

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

  #[NoDiscard]
  public function getTotal(): float
  {
    /** @var float */
    $price = $this->__get('price');

    return $price * $this->quantity;
  }

  #[NoDiscard]
  public function getTotalVes(): float
  {
    /** @var float */
    $priceVES = $this->__get('price_ves');

    return $priceVES * $this->quantity;
  }
}
