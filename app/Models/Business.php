<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $rif
 * @property-read string $address
 * @property-read string $phone
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Repair> $repairs
 * @property-read Collection<int, Layaway> $layaways
 */
final class Business extends Model
{
  public $timestamps = false;
  protected $fillable = ['name', 'rif', 'address', 'phone'];

  /** @return HasMany<Sale, $this> */
  public function sales(): HasMany
  {
    return $this->hasMany(Sale::class);
  }

  /** @return HasMany<Repair, $this> */
  public function repairs(): HasMany
  {
    return $this->hasMany(Repair::class);
  }

  /** @return HasMany<Layaway, $this> */
  public function layaways(): HasMany
  {
    return $this->hasMany(Layaway::class);
  }
}
