<?php

declare(strict_types=1);

namespace App;

/** @param callable(string $file): void $callable */
function foreach_file(string $pattern, callable $callable): void
{
  $files = (array) glob($pattern);

  foreach (array_filter($files) as $file) {
    $callable($file);
  }
}
