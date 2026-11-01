<?php

declare(strict_types=1);

/**
 * @var string $text
 * @var 'primary'|'secondary'|'danger' $variant
 * @var ?string $href
 * @var ?string $onclick
 * @var string $type
 * @var bool $disabled
 * @var bool $small
 * @var ?string $id
 * @var ?string $style
 * @var string $class
 * @var ?string $target
 */

$variant ??= 'primary';
$href ??= null;
$onclick ??= null;
$type ??= 'button';
$disabled ??= false;
$small ??= false;
$id ??= null;
$style ??= null;
$class ??= '';
$target ??= null;

$classes = ['btn', "btn-$variant"];

if ($small) {
  $classes[] = 'btn-sm';
}

if ($class) {
  $classes[] = $class;
}

$classAttr = implode(' ', $classes);

?>

<?php if ($href): ?>
  <a
    class="<?= $classAttr ?>"
    href="<?= $href ?>"
    <?= $target ? "target='$target'" : '' ?>
    <?= $id ? "id='$id'" : '' ?>
    <?= $onclick ? "@click='$onclick'" : '' ?>
    <?= $style ? "style='$style'" : '' ?>>
    <?= $text ?>
  </a>
<?php else: ?>
  <button
    type="<?= $type ?>"
    class="<?= $classAttr ?>"
    <?= $disabled ? 'disabled' : '' ?>
    <?= $id ? "id='$id'" : '' ?>
    <?= $onclick ? "@click='$onclick'" : '' ?>
    <?= $style ? "style='$style'" : '' ?>>
    <?= $text ?>
  </button>
<?php endif ?>
