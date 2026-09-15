<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $stock
 * @property-read Business $business
 */
final class Batch extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['business_id', 'stock'];

  public function business(): BelongsTo
  {
    return $this->belongsTo(Business::class);
  }
}
