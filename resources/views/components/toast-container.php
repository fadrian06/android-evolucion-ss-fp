<?php

declare(strict_types=1);

use Leaf\Flash;
use Leaf\Lingo;

/** @var Lingo $lingo */

$flashTypes = [
  ['key' => 'errors', 'class' => 'toast--error'],
  ['key' => 'warnings', 'class' => 'toast--warning'],
  ['key' => 'successes', 'class' => 'toast--success'],
  ['key' => 'notes', 'class' => 'toast--note'],
];

?>

<div class="toasts d-print-none">
  <?php foreach ($flashTypes as ['key' => $key, 'class' => $class]): ?>
    <?php foreach ((array) Flash::display($key) as $message): ?>
      <div class="toast <?= $class ?>">
        <?= $lingo->translate($message) ?>
      </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
