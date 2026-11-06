<?php

declare(strict_types=1);

/** @var 'danger'|'warning'|'success'|'' */
$variant ??= '';

/** @var string */
$text ??= '';

/** @var string */
$title ??= '';

?>

<abbr
  class="status status-<?= $variant ?>"
  :data-title="'<?= $title ?>'">
  <?= $text ?>
</abbr>
