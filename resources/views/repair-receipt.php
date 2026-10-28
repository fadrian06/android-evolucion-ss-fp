<?php

declare(strict_types=1);

use App\Models\Repair;
use App\Support\PaymentMethod;

/** @var Repair $repair */

?>

<section class="receipt">
  <header class="receipt-head">
    <div>
      <span class="brand-auth"><?= strtolower($_ENV['APP_NAME']) ?></span>
      <h1><?= $repair->business->name ?></h1>
      <p>
        <?= $repair->business->rif ?>
        <br>
        <?= $repair->business->address ?> · <?= $repair->business->phone ?>
      </p>
    </div>
    <div>
      <span class="eyebrow">COMPROBANTE</span>
      <h2>#<?= $repair->id ?></h2>
      <p>Retiro límite: <?= $repair->due_date->format('d/m/Y') ?></p>
    </div>
  </header>

  <dl class="receipt-meta">
    <dt>Cliente</dt>
    <dd><?= $repair->client->name ?></dd>
    <dt>Cédula</dt>
    <dd><?= $repair->client->id_card ?></dd>
    <dt>Descripción</dt>
    <dd><?= $repair->description ?></dd>
    <dt>Total acordado</dt>
    <dd>
      $<?= number_format($repair->price, 2, ',', '.') ?>
      <small>
        (ref. Bs. <?= number_format($repair->price_ves, 2, ',', '.') ?>
      </small>
    </dd>
    <dt>Pagado</dt>
    <dd>$<?= number_format($repair->getTotalPaid(), 2, ',', '.') ?></dd>
    <dt>Pago pendiente</dt>
    <dd>$<?= number_format($repair->getRemainingAmount(), 2, ',', '.') ?></dd>

    <?php foreach ($repair->payments as $payment): ?>
      <dt><?= $payment->method ?></dt>
      <dd><?= PaymentMethod::isVes($payment->method)
        ? 'Bs. ' . number_format($payment->amount, 2, ',', '.') . ' · tasa ' . number_format($payment->exchange_rate, 2, ',', '.')
        : '$' . number_format($payment->amount, 2, ',', '.') ?>
      </dd>
    <?php endforeach ?>
  </dl>
</section>

<script>print()</script>
