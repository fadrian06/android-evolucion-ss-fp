<?php

declare(strict_types=1);

use Leaf\Auth;

/** @var Auth $auth */

$email = (string) $auth->user()?->email;

?>

<a class="profile" href="./cerrar-sesion">
  <span class="profile-avatar"><?= strtoupper($email[0]) ?></span>
  <small class="profile-email"><?= $email ?></small>
  <b class="profile-logout">Salir</b>
</a>
