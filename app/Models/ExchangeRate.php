<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $date
 * @property-read string $rate
 */
final class ExchangeRate extends Model
{
  public $timestamps = false;
  protected $fillable = ['date', 'rate'];
}
