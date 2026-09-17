<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string $date
 * @property-read string $rate
 * @property-read User $user
 */
final class ExchangeRate extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['date', 'rate'];

  /** @return BelongsTo<User, $this> */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
