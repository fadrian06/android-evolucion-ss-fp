<?php

declare(strict_types=1);

use App\Models\Sale;

/** @var Sale $invoice */

?>

<section class="card">
  <div class="card-body">
    <h1 class="card-title h3">Factura</h1>
    <dl class="row mb-4">
      <dt class="col-sm-3">Negocio</dt>
      <dd class="col-sm-9"><?= $invoice->business->name ?></dd>
      <dt class="col-sm-3">Factura</dt>
      <dd class="col-sm-9">#<?= $invoice->id ?></dd>
      <dt class="col-sm-3">Fecha</dt>
      <dd class="col-sm-9"><?= $invoice->created_at->format('d/m/Y') ?></dd>
      <dt class="col-sm-3">Cliente</dt>
      <dd class="col-sm-9"><?= $invoice->client->name ?></dd>
    </dl>

    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Identificador</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoice->items as $item): ?>
            <tr>
              <td><?= $item->product->name ?></td>
              <td>$<?= $item->price ?></td>
              <td><?= $item->quantity ?></td>
              <td>
                <?php if ($item->imei1): ?>
                  IMEI 1: <?= $item->imei1 ?><br>
                  IMEI 2: <?= $item->imei2 ?>
                <?php else: ?>
                  <?= $item->code ?>
                <?php endif ?>
              </td>
              <td>$<?= $item->getTotal() ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">Total</th>
            <td>$<?= $invoice->getTotal() ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <p class="mb-0"><strong>Vendedor:</strong> <?= $auth->user()->email ?></p>
  </div>
</section>

<script>print();</script>
