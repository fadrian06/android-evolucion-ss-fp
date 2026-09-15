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
 * @property-read Collection<int, Sale> $sales
 */
final class Business extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['name'];

  /** @return HasMany<Sale, $this> */
  public function sales(): HasMany
  {
    return $this->hasMany(Sale::class);
  }
}
