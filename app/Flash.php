<?php

declare(strict_types=1);

namespace App;

use Leaf\Flash as LeafFlash;

final readonly class Flash
{
  public static function retrieve(FlashKey $key): array
  {
    return (array) LeafFlash::display($key->key());
  }
}
