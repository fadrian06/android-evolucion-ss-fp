<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $amount
 * @property-read string $method
 * @property-read null|string $exchange_rate
 */
final class Payment extends Model
{
  public $timestamps = false;
  protected $fillable = ['amount', 'method', 'exchange_rate'];

  public function getAmountUsd(): float
  {
    if (!PaymentMethod::isVes($this->method)) {
      return (float) $this->amount;
    }

    return round((float) $this->amount / (float) $this->exchange_rate, 2);
  }
}
