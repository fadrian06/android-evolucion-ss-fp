<?php

declare(strict_types=1);

use App\Models\Business;

/** @var ?Business $business */

?>

<div class="business-switcher">
  <span class="eyebrow">LOCAL ACTIVO</span>
  <strong>
    <?= $business?->name ?: 'Selecciona un local' ?>
  </strong>
  <a href="./negocios">Cambiar local →</a>
</div>
