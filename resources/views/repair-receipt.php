<?php

declare(strict_types=1);

use App\Models\Repair;

/** @var Repair $repair */

?>

<section class="card">
  <div class="card-body">
    <h1 class="card-title h3">Comprobante de reparación</h1>
    <dl class="row mb-4">
      <dt class="col-sm-4">Nombre del negocio</dt>
      <dd class="col-sm-8"><?= $repair->business->name ?></dd>
      <dt class="col-sm-4">RIF</dt>
      <dd class="col-sm-8"><?= $repair->business->rif ?></dd>
      <dt class="col-sm-4">Dirección</dt>
      <dd class="col-sm-8"><?= $repair->business->address ?></dd>
      <dt class="col-sm-4">Teléfono</dt>
      <dd class="col-sm-8"><?= $repair->business->phone ?></dd>
      <dt class="col-sm-4">Comprobante</dt>
      <dd class="col-sm-8">#<?= $repair->id ?></dd>
      <dt class="col-sm-4">Fecha límite de pago y retiro</dt>
      <dd class="col-sm-8"><?= $repair->due_date->format('d/m/Y') ?></dd>
      <dt class="col-sm-4">Cliente</dt>
      <dd class="col-sm-8"><?= $repair->client->name ?></dd>
      <dt class="col-sm-4">Cédula</dt>
      <dd class="col-sm-8"><?= $repair->client->cedula ?></dd>
      <dt class="col-sm-4">Teléfono</dt>
      <dd class="col-sm-8"><?= $repair->client->phone ?></dd>
      <dt class="col-sm-4">Dirección</dt>
      <dd class="col-sm-8"><?= $repair->client->address ?></dd>
      <dt class="col-sm-4">Descripción</dt>
      <dd class="col-sm-8"><?= $repair->description ?></dd>
      <dt class="col-sm-4">Total</dt>
      <dd class="col-sm-8">Bs. <?= number_format((float) $repair->price_ves, 2, ',', '.') ?></dd>
      <dt class="col-sm-4">Pagado</dt>
      <dd class="col-sm-8">Bs. <?= number_format($repair->getTotalPaidVes(), 2, ',', '.') ?></dd>
      <dt class="col-sm-4">Saldo pendiente</dt>
      <dd class="col-sm-8">Bs. <?= number_format($repair->getRemainingAmountVes(), 2, ',', '.') ?></dd>
    </dl>
  </div>
</section>
