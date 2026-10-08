<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class PaymentDetails
{
  /** @return array{amount: float, method: string, exchange_rate: null|float, amount_usd: float} */
  public static function fromInput(
    mixed $amount,
    mixed $method,
    ?float $exchangeRate,
  ): array {
    if (!is_numeric($amount) || (float) $amount <= 0) {
      throw new InvalidArgumentException('El monto debe ser mayor que cero');
    }

    if (!is_string($method) || !in_array($method, PaymentMethod::all(), true)) {
      throw new InvalidArgumentException('El método de pago no es válido');
    }

    $amount = round((float) $amount, 2);

    if (!PaymentMethod::isVes($method)) {
      return [
        'amount' => $amount,
        'method' => $method,
        'exchange_rate' => null,
        'amount_usd' => $amount,
      ];
    }

    if (!$exchangeRate || $exchangeRate <= 0) {
      throw new InvalidArgumentException(
        'Debes establecer una cotización personalizada antes de registrar un pago en bolívares',
      );
    }

    return [
      'amount' => $amount,
      'method' => $method,
      'exchange_rate' => $exchangeRate,
      'amount_usd' => round($amount / $exchangeRate, 2),
    ];
  }

  /**
   * @return list<array{amount: float, method: string, exchange_rate: null|float, amount_usd: float}>
   */
  public static function fromInputs(
    mixed $amounts,
    mixed $methods,
    ?float $exchangeRate,
  ): array {
    if ($amounts === null && $methods === null) {
      return [];
    }

    if (!is_array($amounts) || !is_array($methods)) {
      throw new InvalidArgumentException('Los datos de los pagos no coinciden');
    }

    $payments = [];

    foreach ($amounts as $index => $amount) {
      if ($amount === null || $amount === '') {
        continue;
      }

      $payments[] = self::fromInput($amount, $methods[$index] ?? null, $exchangeRate);
    }

    return $payments;
  }

  /** @param list<array{amount_usd: float}> $payments */
  public static function totalUsd(array $payments): float
  {
    return round(array_sum(array_column($payments, 'amount_usd')), 2);
  }

  /**
   * @param list<array{amount: float, method: string, exchange_rate: null|float, amount_usd: float}> $payments
   * @return list<array{amount: float, method: string, exchange_rate: null|float}>
   */
  public static function forPersistence(array $payments): array
  {
    return array_map(
      static fn(array $payment): array => [
        'amount' => $payment['amount'],
        'method' => $payment['method'],
        'exchange_rate' => $payment['exchange_rate'],
      ],
      $payments,
    );
  }
}
