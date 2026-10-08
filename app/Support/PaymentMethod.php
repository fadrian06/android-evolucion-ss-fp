<?php

declare(strict_types=1);

namespace App\Support;

final class PaymentMethod
{
  public const USD_CASH = 'Efectivo USD';
  public const VES_CASH = 'Efectivo Bs.';
  public const CARD = 'Punto';
  public const TRANSFER = 'Transferencia';

  /** @return list<string> */
  public static function all(): array
  {
    return [
      self::USD_CASH,
      self::VES_CASH,
      self::CARD,
      self::TRANSFER,
    ];
  }

  public static function isVes(string $method): bool
  {
    return $method !== self::USD_CASH;
  }
}
