<?php

declare(strict_types=1);

namespace App;

/** @param callable(string $file): void $callable */
function foreach_file(string $pattern, callable $callable): void
{
  $files = glob($pattern);

  if (!$files) {
    return;
  }

  foreach ($files as $file) {
    $callable($file);
  }
}
