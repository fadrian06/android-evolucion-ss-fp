<?php

declare(strict_types=1);

use Leaf\Auth;

/**
 * @var Auth $auth
 * @var string $slot
 */

?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <base href="<?= str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) ?>">
  <link rel="icon" href="./resources/images/favicon.svg">
  <link rel="stylesheet" href="./resources/styles/app.css">
  <title>Orion · Gestión comercial</title>
</head>
<body>
  <?php Flight::render('components/toast-container') ?>

  <?php if ($auth->user()): ?>
    <div class="app-shell">
      <aside class="sidebar d-print-none">
        <?php Flight::render('components/brand') ?>
        <?php Flight::render('components/business-switcher') ?>
        <?php Flight::render('components/nav') ?>

        <a class="profile" href="./cerrar-sesion"><span><?= strtoupper(substr($auth->user()->email, 0, 1)) ?></span><small><?= htmlspecialchars($auth->user()->email, ENT_QUOTES, 'UTF-8') ?></small><b>Salir</b></a>
      </aside>
      <main class="content"><?= $slot ?></main>
    </div>
  <?php else: ?>
    <main class="auth-shell"><?= $slot ?></main>
  <?php endif ?>
  <script>
    setTimeout(() => {
      document.querySelectorAll('.toast').forEach(toast => {
        toast.classList.add('is-leaving');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
      });
    }, 5000);
  </script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
