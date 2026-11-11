<?php

declare(strict_types=1);

namespace App;

use NoDiscard;

enum FlashKey
{
  case Errors;
  case Warnings;
  case Successes;
  case Notes;
  case RepairReceipt;
  case SaleIds;

  #[NoDiscard]
  public function key(): string
  {
    return strtolower($this->name);
  }

  #[NoDiscard]
  public function class(): string
  {
    return 'toast ' . match ($this) {
      self::Errors => 'toast-error',
      self::Warnings => 'toast-warning',
      self::Successes => 'toast-success',
      self::Notes => 'toast-note',
      default => '',
    };
  }

  /** @return self[] */
  #[NoDiscard]
  public static function messages(): array
  {
    return [self::Errors, self::Notes, self::Successes, self::Warnings];
  }
}
