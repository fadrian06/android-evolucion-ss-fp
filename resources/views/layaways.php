<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Layaway;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Layaway> $layaways
 * @var Collection<int, Client> $clients
 * @var Collection<int, Product> $products
 */

?>

<form method="post" id="register-layaway"></form>
<?php foreach ($layaways as $layaway): ?>
  <form
    method="post"
    id="pay-layaway-<?= $layaway->id ?>"
    action="./apartados/<?= $layaway->id ?>/pagar"></form>
  <form
    method="post"
    id="cancel-layaway-<?= $layaway->id ?>"
    action="./apartados/<?= $layaway->id ?>/cancelar"></form>
<?php endforeach ?>

<div class="table-responsive">
  <table class="table table-hover table-borderless caption-top align-middle">
    <caption>Productos apartados</caption>
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Teléfono</th>
        <th>IMEI 1</th>
        <th>IMEI 2</th>
        <th>Precio USD</th>
        <th>Precio Bs.</th>
        <th>Pagado USD</th>
        <th>Saldo USD</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($layaways as $layaway): ?>
        <tr>
          <td><?= $layaway->client->name ?></td>
          <td><?= $layaway->product->name ?></td>
          <td><?= $layaway->imei1 ?></td>
          <td><?= $layaway->imei2 ?></td>
          <td>$<?= $layaway->price ?></td>
          <td>Bs. <?= number_format((float) $layaway->price_ves, 2, ',', '.') ?></td>
          <td>$<?= $layaway->getTotalPaid() ?></td>
          <td>$<?= $layaway->getRemainingAmount() ?></td>
          <td>
            <?php if ($layaway->cancelled_at): ?>
              <span class="badge text-bg-danger">Cancelado</span>
            <?php elseif ($layaway->getRemainingAmount() > 0): ?>
              <span class="badge text-bg-warning">Pendiente</span>
            <?php else: ?>
              <span class="badge text-bg-success">Pagado</span>
            <?php endif ?>
          </td>
          <td>
            <?php if (!$layaway->cancelled_at): ?>
              <button
                form="cancel-layaway-<?= $layaway->id ?>"
                onclick="return confirm('¿Cancelar este apartado y restaurar el stock?')"
                class="btn btn-danger w-100">
                Cancelar
              </button>
            <?php endif ?>
          </td>
        </tr>
        <?php if (!$layaway->cancelled_at && $layaway->getRemainingAmount() > 0): ?>
          <tr>
            <td colspan="6"></td>
            <td>
              <input
                form="pay-layaway-<?= $layaway->id ?>"
                name="amount"
                type="number"
                min="1"
                max="<?= $layaway->getRemainingAmount() ?>"
                required
                class="form-control"
                placeholder="Monto USD">
            </td>
            <td>
              <select form="pay-layaway-<?= $layaway->id ?>" name="method" required class="form-select">
                <option value="" selected disabled>Método</option>
                <option>Físico</option>
                <option>Punto</option>
                <option>Transferencia</option>
              </select>
            </td>
            <td colspan="2">
              <button form="pay-layaway-<?= $layaway->id ?>" class="btn btn-primary w-100">Pagar</button>
            </td>
          </tr>
        <?php endif ?>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td>
          <select form="register-layaway" name="client_id" required class="form-select">
            <option value="" selected disabled>Cliente</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?= $client->id ?>"><?= $client->name ?></option>
            <?php endforeach ?>
          </select>
        </td>
        <td>
          <select form="register-layaway" name="product_id" required class="form-select">
            <option value="" selected disabled>Teléfono</option>
            <?php foreach ($products as $product): ?>
              <option value="<?= $product->id ?>"><?= $product->name ?></option>
            <?php endforeach ?>
          </select>
        </td>
        <td>
          <input
            form="register-layaway"
            name="imei1"
            required
            class="form-control"
            placeholder="IMEI 1">
        </td>
        <td>
          <input
            form="register-layaway"
            name="imei2"
            required
            class="form-control"
            placeholder="IMEI 2">
        </td>
        <td colspan="6">
          <button form="register-layaway" class="btn btn-primary w-100">
            <span class="bi bi-plus-lg"></span>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
