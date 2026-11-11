<?php

declare(strict_types=1);

namespace App;

use Leaf\Http\Session as HttpSession;
use NoDiscard;

final readonly class Session
{
  #[NoDiscard]
  public static function get(SessionKey $key): mixed
  {
    return HttpSession::get($key->key());
  }

  public static function unset(SessionKey $key): void
  {
    HttpSession::unset($key->key());
  }

  public static function set(SessionKey $key, mixed $value): void
  {
    HttpSession::set($key->key(), $value);
  }
}
