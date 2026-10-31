<?php

declare(strict_types=1);

/**
 * @var string $eyebrow
 * @var string $title
 * @var string $description
 * @var ?string $buttonText
 * @var ?string $buttonAction
 */

$buttonText ??= null;
$buttonAction ??= null;

?>

<header class="page-head">
  <div>
    <?php Flight::render('components/eyebrow', ['text' => $eyebrow]) ?>

    <h1 class="page-head-title"><?= $title ?></h1>
    <p class="page-head-description"><?= $description ?></p>
  </div>

  <?php if ($buttonText && $buttonAction): ?>
    <button
      class="btn btn-primary"
      onclick="<?= $buttonAction ?>">
      <?= $buttonText ?>
    </button>
  <?php endif ?>
</header>
