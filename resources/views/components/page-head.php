<?php

declare(strict_types=1);

/**
 * @var string $eyebrow
 * @var string $title
 * @var string $description
 * @var ?string $buttonText
 * @var ?string $buttonAction
 */

?>

<header class="page-head">
  <div>
    <span class="eyebrow"><?= $eyebrow ?></span>
    <h1 class="page-head-title"><?= $title ?></h1>
    <p class="page-head-description"><?= $description ?></p>
  </div>

  <?php if (isset($buttonText) && isset($buttonAction)): ?>
    <button
      class="btn btn-primary"
      onclick="<?= $buttonAction ?>">
      <?= $buttonText ?>
    </button>
  <?php endif ?>
</header>
