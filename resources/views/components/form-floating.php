<?php

declare(strict_types=1);

$type ??= 'text';
$name ??= '';
$required ??= false;
$label ??= '';

?>

<div class="form-floating">
  <?php Flight::render('components/form-control', [
    'type' => $type,
    'name' => $name,
    'required' => $required,
    'placeholder' => '',
  ]) ?>
  <label><?= $label ?></label>
</div>
