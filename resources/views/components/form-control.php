<?php

declare(strict_types=1);

$type ??= 'text';
$name ??= '';
$required ??= false;
$form ??= '';
$value ??= '';
$min ??= null;
$placeholder ??= null;
$onInput ??= 'isValid = $el.value ? $el.checkValidity() : undefined';
$additionalOnInput ??= '';
$disabled ??= false;
$bindMax ??= '';
$model ??= '';

?>

<input
  <?= $type ? "type=\"$type\"" : '' ?>
  <?= $name ? "name=\"$name\"" : '' ?>
  <?= is_string($placeholder) ? "placeholder=\"$placeholder\"" : '' ?>
  <?= $form ? "form=\"$form\"" : '' ?>
  <?= $value ? "value=\"$value\"" : '' ?>
  <?= is_int($min) ? "min=\"$min\"" : '' ?>
  <?= $onInput ? "@input=\"$onInput;$additionalOnInput\"" : '' ?>
  <?= $bindMax ? ":max=\"$bindMax\"" : '' ?>
  <?= $model ? "x-model=\"$model\"" : '' ?>
  <?= $required ? 'required' : '' ?>
  <?= $disabled ? 'disabled' : '' ?>
  class="form-control"
  :class="{ 'is-valid': isValid, 'is-invalid': isValid === false }"
  x-data="{ isValid: undefined }">
