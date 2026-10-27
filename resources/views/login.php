<section class="auth-card card">
  <div class="brand-auth"><?= strtolower($_ENV['APP_NAME']) ?></div>
  <h1 class="auth-card-title">Bienvenido de vuelta</h1>
  <p class="auth-card-subtitle">Inicia sesión para gestionar tu local.</p>

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
        autocomplete="current-password"
        placeholder="••••••••">
    </div>

    <button class="btn btn-primary">Iniciar sesión</button>
  </form>

  <p class="auth-card-subtitle auth-card-footer">
    ¿No tienes cuenta?
    <a class="auth-card-link" href="./registrarse">Regístrate</a>
  </p>
</section>
