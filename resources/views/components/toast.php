<?php

declare(strict_types=1);

/**
 * @var 'error'|'warning'|'success'|'note' $type
 * @var string $body
 */

$types = [
  'error' => 'Error',
  'warning' => 'Advertencia',
  'success' => 'Operación exitosa',
  'note' => 'Nota',
];

$colors = [
  'error' => 'danger',
  'warning' => 'warning',
  'success' => 'success',
  'note' => 'info',
];

?>

<div class="toast text-bg-<?= $colors[$type] ?>" x-init="new bootstrap.Toast($el).show()">
  <div class="toast-header">
    <strong class="me-auto"><?= $types[$type] ?></strong>
    <button class="btn-close" data-bs-dismiss="toast"></button>
  </div>
  <div class="toast-body"><?= $body ?></div>
</div>
