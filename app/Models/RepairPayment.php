<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use NoDiscard;

/**
 * @property-read float $amount
 * @property-read string $method
 * @property-read ?float $exchange_rate
 * @mixin Builder<static>
 */
final class RepairPayment extends Model
{
  public $timestamps = false;
  protected $fillable = ['amount', 'method', 'exchange_rate'];
  protected $casts = ['amount' => 'float', 'exchange_rate' => 'float'];

  #[NoDiscard]
  public function getAmountUsd(): float
  {
    /** @var float */
    $amount = $this->__get('amount');

    /** @var string */
    $method = $this->__get('method');

    /** @var float */
    $exchangeRate = $this->__get('exchange_rate');

    if (!PaymentMethod::isVes($method)) {
      return $amount;
    }

    return $amount / $exchangeRate;
  }
}
