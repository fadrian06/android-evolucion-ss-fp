<?php

declare(strict_types=1);

use App\Models\Business;
use Leaf\Auth;
use Leaf\Flash;
use Leaf\Lingo;

/**
 * @var Auth $auth
 * @var string $slot
 * @var ?Business $business
 * @var Lingo $lingo
 */

$navbarId = uniqid();

$navItems = [
  ['href' => './clientes', 'slot' => 'Clientes'],
  ['href' => './productos', 'slot' => 'Productos'],
  ['href' => './ventas', 'slot' => 'Ventas'],
  ['href' => './apartados', 'slot' => 'Apartados'],
  ['href' => './reparaciones', 'slot' => 'Reparaciones'],
  ['href' => './negocios', 'slot' => 'Negocios'],
  ['href' => './calculadora', 'slot' => 'Calculadora'],
];

$requestUrl = Flight::request()->url;

$flashTypes = [
  [
    'key' => 'errors',
    'type' => 'error',
  ],
  [
    'key' => 'warnings',
    'type' => 'warning',
  ],
  [
    'key' => 'successes',
    'type' => 'success',
  ],
  [
    'key' => 'notes',
    'type' => 'note',
  ],
];

?>

<!doctype html>
<html
  x-data="{
    theme: matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light',
  }"
  x-init="
    matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
      theme = event.matches ? 'dark' : 'light';
    });
  "
  :data-bs-theme="theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <base href="<?= str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
  <div class="toast-container position-fixed bottom-0 end-0 p-5 d-print-none">
    <?php foreach ($flashTypes as ['key' => $key, 'type' => $type]): ?>
      <?php foreach (((array) Flash::display($key)) as $body): ?>
        <?php Flight::render('components/toast', ['type' => $type, 'body' => $lingo->translate($body)]) ?>
      <?php endforeach ?>
    <?php endforeach ?>
  </div>

  <nav class="navbar navbar-expand-xl d-print-none">
    <div class="container">
      <?php if (isset($business)): ?>
        <span class="navbar-brand"><?= $business->name ?></span>
      <?php endif ?>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#<?= $navbarId ?>">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="<?= $navbarId ?>">
        <ul class="navbar-nav mb-2 mb-xl-0">
          <?php foreach ($navItems as $navItem): ?>
            <li class="nav-item">
              <a
                href="<?= $navItem['href'] ?>"
                class="
                  nav-link
                  <?= $navItem['href'] !== ".$requestUrl" ? '' : 'active' ?>
                  <?= $auth->user() && isset($business) ? '' : 'disabled' ?>
                ">
                <?= $navItem['slot'] ?>
              </a>
            </li>
          <?php endforeach ?>
        </ul>
        <ul class="navbar-nav ms-auto mb-2 mb-xl-0 align-items-xl-center">
          <?php if ($auth->user()): ?>
            <li class="nav-item">
              <?= $auth->user()->email ?>
            </li>
            <li class="nav-item">
              <a href="./cerrar-sesion" class="nav-link">
                <span class="bi bi-box-arrow-right"></span></span>
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a
                href="./ingresar"
                class="
                  nav-link
                  <?= !str_contains('/ingresar', $requestUrl) ? '' : 'disabled' ?>
                ">
                <span class="bi bi-box-arrow-in-right"></span>
              </a>
            </li>
            <li class="nav-item">
              <a
                href="./registrarse"
                class="
                  nav-link
                  <?= !str_contains('./registrarse', $requestUrl) ? '' : 'disabled' ?>
                ">
                <span class="bi bi-person-plus"></span>
              </a>
            </li>
          <?php endif ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container">
    <?= $slot ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.17.3/dist/cdn.min.js"></script>
</body>

</html>
