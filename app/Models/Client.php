<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read int $id
 * @property-read string $name
 */
final class Client extends Model
{
  #[Override]
  public $timestamps = false;

  #[Override]
  protected $fillable = ['name'];
}
