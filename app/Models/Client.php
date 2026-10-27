<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $id_card
 * @property-read string $phone
 * @property-read string $address
 * @property-read int $sales_count
 * @property-read int $repairs_count
 * @property-read int $layaways_count
 */
final class Client extends Model
{
  public $timestamps = false;
  protected $fillable = ['name', 'id_card', 'phone', 'address'];

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

  public function hasAssociatedRecords(): bool
  {
    $associatedRecords = (
      $this->sales_count
      + $this->repairs_count
      + $this->layaways_count
    );

    return $associatedRecords > 0;
  }
}
