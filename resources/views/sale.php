<?php

declare(strict_types=1);

use App\Models\Sale;

/** @var Sale $sale */

?>

<section class="receipt">
  <header class="receipt-head">
    <div>
      <?php Flight::render('components/brand-auth') ?>

      <h1><?= $sale->business->name ?></h1>
      <p>
        <?= $sale->business->rif ?> <br>
        <?= $sale->business->address ?> · <?= $sale->business->phone ?>
      </p>
    </div>
    <div>
      <?php Flight::render('components/eyebrow', ['text' => 'COMPROBANTE']) ?>

      <p><?= $sale->created_at->format('d/m/Y') ?></p>

      <?php if ($sale->cancelled_at): ?>
        <?php Flight::render('components/status', [
          'variant' => 'danger',
          'text' => 'ANULADA',
        ]) ?>
      <?php endif ?>
    </div>
  </header>

  <dl class="receipt-meta">
    <dt>Cliente</dt>
    <dd><?= $sale->client->name ?></dd>
    <dt>Cédula</dt>
    <dd><?= $sale->client->id_card ?></dd>
    <dt>Contacto</dt>
    <dd><?= $sale->client->phone ?></dd>
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
      <?php foreach ($sale->items as $item): ?>
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
      Total: Bs. <?= number_format($sale->getTotalVes(), 2, ',', '.') ?>
    </strong>
  </div>
</section>

<script>print()</script>
