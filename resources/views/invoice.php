<?php

declare(strict_types=1);

use App\Models\Sale;

/** @var Sale $invoice */

?>

<section class="receipt">
  <header class="receipt-head">
    <div>
      <span class="brand-auth"><?= strtolower($_ENV['APP_NAME']) ?></span>
      <h1><?= $invoice->business->name ?></h1>
      <p>
        <?= $invoice->business->rif ?> <br>
        <?= $invoice->business->address ?> · <?= $invoice->business->phone ?>
      </p>
    </div>
    <div>
      <span class="eyebrow">COMPROBANTE</span>
      <p><?= $invoice->created_at->format('d/m/Y') ?></p>

      <?php if ($invoice->cancelled_at): ?>
        <span class="status status-danger">ANULADA</span>
      <?php endif ?>
    </div>
  </header>

  <dl class="receipt-meta">
    <dt>Cliente</dt>
    <dd><?= $invoice->client->name ?></dd>
    <dt>Cédula</dt>
    <dd><?= $invoice->client->id_card ?></dd>
    <dt>Contacto</dt>
    <dd><?= $invoice->client->phone ?></dd>
  </dl>

  <table class="data-table">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Identificador</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoice->items as $item): ?>
        <tr>
          <td><?= $item->product->name ?> × <?= $item->quantity ?></td>
          <td>
            <?= $item->imei1
              ? $item->imei1 . ' / ' . $item->imei2
              : $item->code
            ?>
          </td>
          <td>Bs. <?= number_format($item->getTotalVes(), 2, ',', '.') ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div style="text-align:right;margin-top:22px">
    <strong>
      Total: Bs. <?= number_format($invoice->getTotalVes(), 2, ',', '.') ?>
    </strong>
  </div>
</section>

<script>print()</script>
