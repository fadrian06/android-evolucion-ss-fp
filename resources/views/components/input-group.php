<?php

declare(strict_types=1);

$type ??= 'text';
$name ??= '';
$required ??= false;
$form ??= '';
$value ??= '';
$min ??= 0;
$icon ??= '';

?>

<div class="input-group">
  <?php if ($icon): ?>
    <span class="input-group-text <?= $icon ?>"></span>
    <?php Flight::render('components/form-control', [
      'type' => $type,
      'name' => $name,
      'required' => $required,
      'form' => $form,
      'value' => $value,
      'min' => $min,
    ]) ?>
  <?php endif ?>
</div>
