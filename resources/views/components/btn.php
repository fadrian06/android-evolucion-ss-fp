<?php

declare(strict_types=1);

/** @var string */
$text ??= '';

/** @var 'primary'|'secondary'|'danger' */
$variant ??= 'primary';

/** @var string */
$href ??= '';

/** @var string */
$onclick ??= '';

/** @var 'button'|'submit' */
$type ??= 'button';

/** @var bool */
$disabled ??= false;

/** @var bool */
$small ??= false;

/** @var string */
$id ??= '';

/** @var string */
$style ??= '';

/** @var string */
$class ??= '';

/** @var '_self'|'_blank'|'_parent'|'_top' */
$target ??= '_self';

/** @var string */
$title ??= '';

$classes = ['btn', "btn-$variant"];

if ($small) {
  $classes[] = 'btn-sm';
}

if ($class) {
  $classes[] = $class;
}

$class = implode(' ', $classes);

?>

<?php if ($href): ?>
  <a
    class="<?= $class ?>"
    <?= $disabled ? 'aria-disabled' : "href='$href'" ?>
    target="<?= $target ?>"
    <?= $id ? "id='$id'" : '' ?>
    <?= $onclick ? "@click='$onclick'" : '' ?>
    style="<?= $style ?>"
    title="<?= $title ?>">
    <?= $text ?>
  </a>
<?php else: ?>
  <button
    type="<?= $type ?>"
    class="<?= $class ?>"
    <?= $disabled ? 'disabled' : '' ?>
    <?= $id ? "id='$id'" : '' ?>
    <?= $onclick ? "@click='$onclick'" : '' ?>
    style="<?= $style ?>"
    title="<?= $title ?>">
    <?= $text ?>
  </button>
<?php endif ?>
