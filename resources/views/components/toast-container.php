<?php

declare(strict_types=1);

use App\Flash;
use App\FlashKey;
use Leaf\Lingo;

/** @var Lingo $lingo */

?>

<div class="toasts d-print-none">
  <?php foreach (FlashKey::messages() as $key): ?>
    <?php foreach (Flash::retrieve($key) as $message): ?>
      <div
        x-data="{ visible: false }"
        x-init="
          $nextTick(() => visible = true);
          setTimeout(() => visible = false, 5000);
        "
        x-show="visible"
        x-transition
        class="<?= $key->class() ?>">
        <?= $lingo->translate($message) ?>
      </div>
    <?php endforeach ?>
  <?php endforeach ?>

  <template x-for="(message, index) in messages || []">
    <div
      x-data="{ visible: true }"
      x-init="
        $nextTick(() => visible = true);
        setTimeout(() => visible = false, 5000);
      "
      x-show="visible"
      x-transition
      :class="{
        <?php foreach (FlashKey::messages() as $key): ?>
          '<?= $key->class() ?>': message.key === '<?= $key->key() ?>',
        <?php endforeach ?>
      }"
      x-text="message.text">
    </div>
  </template>
</div>
