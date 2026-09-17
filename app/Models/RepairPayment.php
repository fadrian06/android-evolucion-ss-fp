<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $amount_ves
 * @property-read string $method
 */
final class RepairPayment extends Model
{
  public $timestamps = false;
  protected $fillable = ['amount_ves', 'method'];
}
