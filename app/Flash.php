<?php

declare(strict_types=1);

namespace App;

use Leaf\Flash as LeafFlash;

final readonly class Flash
{
  /** @return string[] */
  public static function retrieve(FlashKey $key): array
  {
    return (array) LeafFlash::display($key->key());
  }

  /** @param string[] $messages */
  public static function set(array $messages, FlashKey $key): void
  {
    LeafFlash::set($messages, $key->key());
  }
}
