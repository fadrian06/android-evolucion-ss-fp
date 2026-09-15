<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property-read int $id
 * @property string $email
 * @property string $password
 * @property-read Collection<int, Business> $businesses
 * @property-read Collection<int, Client> $clients
 * @property-read Collection<int, Product> $products
 */
final class User extends Model
{
  #[Override]
  public $timestamps = false;

  public function businesses(): HasMany
  {
    return $this->hasMany(Business::class);
  }

  public function clients(): HasMany
  {
    return $this->hasMany(Client::class);
  }

  public function products(): HasMany
  {
    return $this->hasMany(Product::class);
  }
}
