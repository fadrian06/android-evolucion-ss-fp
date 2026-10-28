<?php

declare(strict_types=1);

use App\FlashKey;
use Leaf\Auth;

/**
 * @var Auth $auth
 * @var string $slot
 */

?>

<!doctype html>
<html
  x-data="{ messages: [] }"
  @error.window="
    messages.push({
      key: '<?= FlashKey::Errors->key() ?>',
      text: $event.error.message,
    });

    <?php // TODO: Send error to backend for logging ?>
  ">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <meta name="color-scheme" content="light dark">
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
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.17.5/dist/cdn.min.js">
  </script>
</head>

<body>
  <?php Flight::render('components/toast-container') ?>

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
