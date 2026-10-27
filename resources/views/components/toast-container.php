<?php

declare(strict_types=1);

use App\FlashKey;
use Leaf\Flash;
use Leaf\Lingo;

/** @var Lingo $lingo */

?>

<div class="toasts d-print-none">
  <?php foreach (FlashKey::cases() as $flashKey): ?>
    <?php foreach ((array) Flash::display($flashKey->key()) as $message): ?>
      <div class="<?= $flashKey->class() ?>">
        <?= $lingo->translate($message) ?>
      </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
