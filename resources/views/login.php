<form method="post" class="col-xl-3 m-auto mt-5 d-grid gap-3">
  <div class="input-group">
    <span class="input-group-text bi bi-envelope-at-fill"></span>
    <?php Flight::render('components/form-floating', [
      'name' => 'email',
      'type' => 'email',
      'required' => true,
      'label' => 'Correo electrónico',
    ]) ?>
  </div>
  <div class="input-group">
    <span class="input-group-text bi bi-lock-fill"></span>
    <?php Flight::render('components/form-floating', [
      'name' => 'password',
      'type' => 'password',
      'required' => true,
      'label' => 'Contraseña',
    ]) ?>
  </div>
  <input type="submit" value="Iniciar sesión" class="btn btn-primary w-100">
</form>
