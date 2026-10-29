<?php

declare(strict_types=1);

/**
 * @var string $title
 * @var bool $inDialog
 */

$inDialog ??= false;

?>

<div class="toolbar" style="<?= $inDialog ? 'margin:-22px -22px 6px' : '' ?>">
  <h2 class="toolbar-title"><?= $title ?></h2>

  <?php if ($inDialog): ?>
    <button
      type="button"
      class="btn btn-secondary btn-sm"
      onclick="this.closest('dialog').close()">
      Cancelar
    </button>
  <?php endif ?>
</div>
