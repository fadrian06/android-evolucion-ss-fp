<?php

declare(strict_types=1);

use App\Flash;
use App\FlashKey;
use Leaf\Lingo;

/** @var Lingo $lingo */

?>

<div class="toasts d-print-none">
  <?php foreach (FlashKey::cases() as $key): ?>
    <?php foreach (Flash::retrieve($key) as $message): ?>
      <div class="<?= $key->class() ?>">
        <?= $lingo->translate($message) ?>
      </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
