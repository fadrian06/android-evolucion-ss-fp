<?php

declare(strict_types=1);

use Leaf\Auth;

/** @var Auth $auth */

$redirectGuest = $auth->config('redirect.guest');

?>

<a class="brand" href=".<?= $redirectGuest ?>">
  <span class="brand-mark"><?= strtoupper($_ENV['APP_NAME'][0]) ?></span>
  <?= strtolower($_ENV['APP_NAME']) ?>
</a>
