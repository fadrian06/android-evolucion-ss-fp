<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read int $id
 * @property-read int $amount
 * @property-read string $method
 */
final class Payment extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['amount', 'method'];
}
