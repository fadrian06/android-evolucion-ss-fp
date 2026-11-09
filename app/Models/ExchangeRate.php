<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read DateTimeInterface $date
 * @property-read float $rate
 */
final class ExchangeRate extends Model
{
  public $timestamps = false;
  protected $fillable = ['date', 'rate'];
  protected $casts = ['date' => 'date', 'rate' => 'float'];
}
