<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $amount
 * @property-read string $method
 */
final class Payment extends Model
{
  public $timestamps = false;
  protected $fillable = ['amount', 'method'];
}
