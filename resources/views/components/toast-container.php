<?php

declare(strict_types=1);

use Leaf\Flash;
use Leaf\Lingo;

/** @var Lingo $lingo */

$flashClassesByKey = [
  'errors' => 'toast--error',
  'warnings' => 'toast--warning',
  'successes' => 'toast--success',
  'notes' => 'toast--note',
];

?>

<div class="toasts d-print-none">
  <?php foreach ($flashClassesByKey as $key => $class): ?>
    <?php foreach ((array) Flash::display($key) as $message): ?>
      <div class="toast <?= $class ?>">
        <?= $lingo->translate($message) ?>
      </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
