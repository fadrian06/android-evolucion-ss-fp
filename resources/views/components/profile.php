<?php

declare(strict_types=1);

use Leaf\Auth;

/** @var Auth $auth */

?>

<a class="profile" href="./cerrar-sesion">
  <span>
    <?= strtoupper(substr($auth->user()->email, 0, 1)) ?>
  </span>
  <small>
    <?= $auth->user()->email ?>
  </small>
  <b>Salir</b>
</a>
