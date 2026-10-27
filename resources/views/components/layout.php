<?php

declare(strict_types=1);

use App\Models\Business;
use Leaf\Auth;
use Leaf\Flash;
use Leaf\Lingo;

/** @var Auth $auth */
/** @var string $slot */
/** @var ?Business $business */
/** @var Lingo $lingo */

$path = Flight::request()->url;
$redirectGuest = $auth->config('redirect.guest');

$items = [
  ['href' => ".$redirectGuest", 'label' => 'Inicio', 'icon' => '⌂'],
  ['href' => './ventas', 'label' => 'Ventas', 'icon' => '↗'],
  ['href' => './apartados', 'label' => 'Apartados', 'icon' => '◇'],
  ['href' => './reparaciones', 'label' => 'Reparaciones', 'icon' => '⌕'],
  ['href' => './productos', 'label' => 'Inventario', 'icon' => '▦'],
  ['href' => './clientes', 'label' => 'Clientes', 'icon' => '♙'],
  ['href' => './calculadora', 'label' => 'Calculadora', 'icon' => '⊞'],
  ['href' => './negocios', 'label' => 'Locales', 'icon' => '⌖'],
];

$flashTypes = [
  ['key' => 'errors', 'class' => 'toast--error'],
  ['key' => 'warnings', 'class' => 'toast--warning'],
  ['key' => 'successes', 'class' => 'toast--success'],
  ['key' => 'notes', 'class' => 'toast--note'],
];

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
  <div class="toasts d-print-none">
    <?php foreach ($flashTypes as ['key' => $key, 'class' => $class]): ?>
      <?php foreach ((array) Flash::display($key) as $message): ?>
        <div class="toast <?= $class ?>">
          <?= $lingo->translate($message) ?>
        </div>
      <?php endforeach ?>
    <?php endforeach ?>
  </div>

  <?php if ($auth->user()): ?>
    <div class="app-shell">
      <aside class="sidebar d-print-none">
        <a class="brand" href=".<?= $redirectGuest ?>">
          <span class="brand-mark">O</span>
          <span>orion</span>
        </a>

        <div class="business-switcher">
          <span class="eyebrow">LOCAL ACTIVO</span>
          <strong>
            <?= $business?->name ?: 'Selecciona un local' ?>
          </strong>
          <a href="./negocios">Cambiar local →</a>
        </div>

        <nav>
          <?php foreach ($items as $item): ?>
            <?php $active = $path === '/' ? $item['href'] === './' : str_contains($item['href'], ltrim($path, '/')) ?>
            <a class="<?= $active ? 'is-active' : '' ?> <?= !isset($business) && $item['href'] !== './negocios' ? 'is-disabled' : '' ?>" href="<?= $item['href'] ?>">
              <span><?= $item['icon'] ?></span><?= $item['label'] ?>
            </a>
          <?php endforeach ?>
          <a class="mobile-logout" href="./cerrar-sesion"><span>↪</span>Salir</a>
        </nav>
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
