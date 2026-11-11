<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $stock
 * @property-read Business $business
 * @mixin Builder<static>
 */
final class Batch extends Model
{
  public $timestamps = false;
  protected $fillable = ['business_id', 'product_id', 'stock'];

  /** @return BelongsTo<Business, $this> */
  public function business(): BelongsTo
  {
    return $this->belongsTo(Business::class);
  }
}
