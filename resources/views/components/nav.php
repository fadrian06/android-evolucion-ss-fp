<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\ExchangeRate;
use Leaf\Auth;

/**
 * @var Auth $auth
 * @var ?Business $business
 * @var ?ExchangeRate $exchangeRate
 */

$redirectGuest = $auth->config('redirect.guest');
$path = Flight::request()->url;

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

?>

<nav>
  <?php foreach ($items as $item): ?>
    <?php
      $active = $path === '/'
        ? $item['href'] === './'
        : str_contains($item['href'], ltrim($path, '/'));

      $disable = !$business && $item['href'] !== './negocios'
        || !$exchangeRate && !in_array($item['href'], [
          './negocios',
          './calculadora',
        ]);
    ?>

    <a
      class="
        <?= $active ? 'is-active' : '' ?>
        <?= $disable ? 'is-disabled' : '' ?>
      "
      href="<?= $item['href'] ?>">
      <span>
        <?= $item['icon'] ?>
      </span>
      <?= $item['label'] ?>
    </a>
  <?php endforeach ?>

  <a class="mobile-logout" href="./cerrar-sesion">
    <span>↪</span>
    Salir
  </a>
</nav>
