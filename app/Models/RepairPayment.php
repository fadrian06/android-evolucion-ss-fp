<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read int $id
 * @property-read string $amount_ves
 * @property-read string $method
 */
final class RepairPayment extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['amount_ves', 'method'];
}
