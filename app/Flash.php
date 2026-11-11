<?php

declare(strict_types=1);

namespace App;

use Leaf\Flash as LeafFlash;
use NoDiscard;

final readonly class Flash
{
  /** @return string[] */
  #[NoDiscard]
  public static function retrieve(FlashKey $key): array
  {
    return (array) LeafFlash::display($key->key());
  }

  public static function set(mixed $message, FlashKey $key): void
  {
    LeafFlash::set($message, $key->key());
  }
}
