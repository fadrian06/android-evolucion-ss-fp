<form method="post" class="col-xl-3 m-auto mt-5 d-grid gap-3">
  <div class="input-group">
    <span class="input-group-text bi bi-envelope-at-fill"></span>
    <?php Flight::render('components/form-floating', [
      'type' => 'email',
      'name' => 'email',
      'required' => true,
      'label' => 'Correo electrónico',
    ]) ?>
  </div>
  <div class="input-group">
    <span class="input-group-text bi bi-lock-fill"></span>
    <?php Flight::render('components/form-floating', [
      'type' => 'password',
      'name' => 'password',
      'required' => true,
      'label' => 'Contraseña',
    ]) ?>
  </div>
  <input type="submit" value="Registrarse" class="btn btn-primary w-100">
</form>
