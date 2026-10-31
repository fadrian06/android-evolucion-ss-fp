<?php

declare(strict_types=1);

?>

<section class="auth-card card">
  <?php Flight::render('components/brand-auth') ?>

  <h1 class="auth-card-title">Crea tu cuenta</h1>
  <p class="auth-card-subtitle">Configura tu espacio de trabajo en minutos.</p>

  <form method="post" class="stack">
    <div class="field">
      <label>Correo electrónico</label>
      <input
        name="email"
        type="email"
        required
        autocomplete="email"
        placeholder="nombre@correo.com">
    </div>

    <div class="field">
      <label>Contraseña</label>
      <input
        name="password"
        type="password"
        required
        autocomplete="new-password"
        placeholder="Crea una contraseña">
    </div>

    <button class="btn btn-primary">Crear cuenta</button>
  </form>

  <p class="auth-card-subtitle auth-card-footer">
    ¿Ya tienes cuenta?
    <a class="auth-card-link" href="./ingresar">Inicia sesión</a>
  </p>
</section>
