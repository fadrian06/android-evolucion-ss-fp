<?php

declare(strict_types=1);

namespace App;

enum FlashKey
{
  case Errors;
  case Warnings;
  case Successes;
  case Notes;

  public function key(): string
  {
    return strtolower($this->name);
  }

  public function class(): string
  {
    return 'toast ' . match ($this) {
      self::Errors => 'toast--error',
      self::Warnings => 'toast--warning',
      self::Successes => 'toast--success',
      self::Notes => 'toast--note',
    };
  }
}
