<?php

declare(strict_types=1);

namespace App;

use NoDiscard;

enum SessionKey
{
  case Business;

  #[NoDiscard]
  public function key(): string
  {
    return strtolower($this->name) . '_id';
  }
}
