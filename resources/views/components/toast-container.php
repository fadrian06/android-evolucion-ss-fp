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
      <div
        x-data="{ leaving: false }"
        x-init="
          setTimeout(() => {
            leaving = true;
          }, 5000);
        "
        @transitionend="$el.remove()"
        class="<?= $key->class() ?>"
        :class="{ 'is-leaving': leaving }">
        <?= $lingo->translate($message) ?>
      </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
