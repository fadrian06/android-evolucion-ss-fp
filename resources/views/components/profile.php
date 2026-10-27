<?php

declare(strict_types=1);

use Leaf\Auth;

/** @var Auth $auth */

?>

<a class="profile" href="./cerrar-sesion">
  <span class="profile-avatar">
    <?= strtoupper(substr($auth->user()->email, 0, 1)) ?>
  </span>
  <small class="profile-email">
    <?= $auth->user()->email ?>
  </small>
  <b class="profile-logout">Salir</b>
</a>
