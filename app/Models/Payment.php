<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use NoDiscard;

final class Payment extends Model
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
