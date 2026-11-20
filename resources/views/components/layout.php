<?php

declare(strict_types=1);

use App\FlashKey;
use App\Models\ExchangeRate;
use Leaf\Auth;

/**
 * @var Auth $auth
 * @var string $slot
 * @var ?ExchangeRate $exchangeRate
 */

$exchangeRate ??= null;

?>

<!doctype html>
<html
  x-data="{
    theme: $persist(matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    messages: [],
    exchangeRate: Number(<?= $exchangeRate?->rate ?: 0 ?>),

    getTotalPaid(payments = []) {
      let total = 0;

      for (const payment of payments) {
        if (!payment.method || payment.method === 'Efectivo USD') {
          total += Number(payment.amount);
        } else {
          total += Number(payment.amount) / Number(this.exchangeRate);
        }
      }

      return total;
    },

    trim(string = '') {
      return string
        .toLowerCase()
        .replace(/\s+/g, '')
        .replace(/[áÁ]/g, 'a')
        .replace(/[éÉ]/g, 'e')
        .replace(/[íÍ]/g, 'i')
        .replace(/[óÓ]/g, 'o')
        .replace(/[úÚ]/g, 'u');
    },

    getPriceInVES(price = 0) {
      return Number(price) * Number(this.exchangeRate);
    },

    ui: $persist({
      show_businesses_filters: {
        value: true,
        name: 'Filtros de locales',
        description: 'Muestra u oculta los filtros de búsqueda rápida de locales.',
      },
      show_clients_filters: {
        value: true,
        name: 'Filtros de clientes',
        description: 'Muestra u oculta los filtros de búsqueda rápida de clientes.',
      },
      show_products_filters: {
        value: true,
        name: 'Filtros de inventario',
        description: 'Muestra u oculta los filtros y buscador del inventario de productos.',
      },
      show_layaways_filters: {
        value: true,
        name: 'Filtros de apartados',
        description: 'Muestra u oculta los filtros y buscador de apartados.',
      },
    }),
  }"
  x-init="
    matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
      theme = event.matches ? 'dark' : 'light';
    });
  "
  @error.window="
    messages.push({
      key: '<?= FlashKey::Errors->key() ?>',
      text: $event.error?.message || $event.message || '',
    });

    <?php // TODO: Send error to backend for logging ?>
  "
  @offline.window="
    messages.push({
      key: '<?= FlashKey::Errors->key() ?>',
      text: 'No hay conexión a internet. Algunas funciones pueden no estar disponibles.',
    });

    <?php // TODO: Disable some features that require internet connection ?>
  "
  @online.window="
    messages.push({
      key: '<?= FlashKey::Successes->key() ?>',
      text: 'Conexión a internet restaurada. Todas las funciones están disponibles.',
    });

    <?php // TODO: Re-enable features that require internet connection ?>
  ">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <meta
    name="color-scheme"
    :content="theme === 'light' ? 'only light' : 'only dark'">
  <title>
    <?= mb_convert_case($_ENV['APP_NAME'], MB_CASE_TITLE) ?>
    · Gestión comercial
  </title>
  <base href="<?= str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) ?>">
  <link rel="icon" href="./resources/images/favicon.svg">
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource-variable/inter@5.3.0/index.css">
  <link rel="stylesheet" href="./resources/styles/app.css">
  <script
    defer
    src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js">
  </script>
  <script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.17.5/dist/cdn.min.js">
  </script>
</head>

<body>
  <?php Flight::render('components/toast-container') ?>
  <?php Flight::render('components/theme-toggler') ?>
  <?php Flight::render('components/ui-toggler') ?>

  <?php if ($auth->user()): ?>
    <div class="app-shell">
      <?php Flight::render('components/sidebar') ?>

      <main class="content"><?= $slot ?></main>
    </div>
  <?php else: ?>
    <main class="auth-shell"><?= $slot ?></main>
  <?php endif ?>
</body>

</html>
