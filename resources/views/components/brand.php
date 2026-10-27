<?php

declare(strict_types=1);

use Leaf\Auth;

/** @var Auth $auth */

$redirectGuest = $auth->config('redirect.guest');

?>

<a class="brand" href=".<?= $redirectGuest ?>">
  <span class="brand-mark">O</span>
  <span>orion</span>
</a>
