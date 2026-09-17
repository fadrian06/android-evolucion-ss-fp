<?php

declare(strict_types=1);

use App\Models\Sale;

/** @var Sale $invoice */

?>

<section class="card">
  <div class="card-body">
    <h1 class="card-title h3">Factura</h1>
    <?php if ($invoice->cancelled_at): ?>
      <p class="alert alert-danger">Factura anulada</p>
    <?php endif ?>
    <dl class="row mb-4">
      <dt class="col-sm-3">Nombre del negocio</dt>
      <dd class="col-sm-9"><?= $invoice->business->name ?></dd>
      <dt class="col-sm-3">RIF</dt>
      <dd class="col-sm-9"><?= $invoice->business->rif ?></dd>
      <dt class="col-sm-3">Dirección</dt>
      <dd class="col-sm-9"><?= $invoice->business->address ?></dd>
      <dt class="col-sm-3">Teléfono</dt>
      <dd class="col-sm-9"><?= $invoice->business->phone ?></dd>
      <dt class="col-sm-3">Factura</dt>
      <dd class="col-sm-9">#<?= $invoice->id ?></dd>
      <dt class="col-sm-3">Fecha</dt>
      <dd class="col-sm-9"><?= $invoice->created_at->format('d/m/Y') ?></dd>
      <dt class="col-sm-3">Nombre del cliente</dt>
      <dd class="col-sm-9"><?= $invoice->client->name ?></dd>
      <dt class="col-sm-3">Cédula</dt>
      <dd class="col-sm-9"><?= $invoice->client->cedula ?></dd>
      <dt class="col-sm-3">Teléfono</dt>
      <dd class="col-sm-9"><?= $invoice->client->phone ?></dd>
      <dt class="col-sm-3">Dirección</dt>
      <dd class="col-sm-9"><?= $invoice->client->address ?></dd>
    </dl>

    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio USD</th>
            <th>Precio Bs.</th>
            <th>Cantidad</th>
            <th>Identificador</th>
            <th>Subtotal USD</th>
            <th>Subtotal Bs.</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoice->items as $item): ?>
            <tr>
              <td><?= $item->product->name ?></td>
              <td>$<?= $item->price ?></td>
              <td>Bs. <?= number_format((float) $item->price_ves, 2, ',', '.') ?></td>
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
              <td>Bs. <?= number_format($item->getTotalVes(), 2, ',', '.') ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="5" class="text-end">Total</th>
            <td>$<?= $invoice->getTotal() ?></td>
            <td>Bs. <?= number_format($invoice->getTotalVes(), 2, ',', '.') ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <p class="mb-0"><strong>Vendedor:</strong> <?= $auth->user()->email ?></p>
  </div>
</section>

<script>print();</script>
