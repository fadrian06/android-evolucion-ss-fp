<?php

declare(strict_types=1);

use App\Models\Business;

/** @var ?Business $business */

?>

<div class="business-switcher">
  <?php if ($business): ?>
    <?php Flight::render('components/eyebrow', ['text' => 'LOCAL ACTIVO']) ?>
  <?php endif ?>

  <strong class="business-switcher-name">
    <?= $business?->name ?: 'Sin local activo' ?>
  </strong>

  <a href="./negocios" class="business-switcher-link">
    <?= $business ? 'Cambiar local' : 'Seleccionar local' ?> →
  </a>
</div>
