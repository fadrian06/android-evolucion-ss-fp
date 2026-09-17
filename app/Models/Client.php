<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $cedula
 * @property-read string $phone
 * @property-read string $address
 */
final class Client extends Model
{
  public $timestamps = false;
  protected $fillable = ['name', 'cedula', 'phone', 'address'];
}
