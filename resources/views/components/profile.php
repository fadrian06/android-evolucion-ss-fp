<?php

declare(strict_types=1);

use Leaf\Auth;

/** @var Auth $auth */

$email = (string) $auth->user()?->email;

?>

<div class="profile">
  <span class="profile-avatar"><?= strtoupper($email[0]) ?></span>
  <small class="profile-email"><?= $email ?></small>
  <a class="profile-logout" href="./cerrar-sesion">Salir</a>
</div>
