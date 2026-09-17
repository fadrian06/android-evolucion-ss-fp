<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Repair;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Repair> $repairs
 * @var Collection<int, Client> $clients
 * @var null|string $receiptId
 */

?>

<?php if ($receiptId): ?>
  <script>open('./reparaciones/<?= $receiptId ?>', '_blank');</script>
<?php endif ?>

<form method="post" id="register-repair"></form>
<?php foreach ($repairs as $repair): ?>
  <form
    method="post"
    id="pay-repair-<?= $repair->id ?>"
    action="./reparaciones/<?= $repair->id ?>/pagar"></form>
<?php endforeach ?>

<div class="table-responsive">
  <table class="table table-hover table-borderless caption-top align-middle">
    <caption>Lista de reparaciones</caption>
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Vence</th>
        <th>Pagado</th>
        <th>Saldo</th>
        <th>Comprobante</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($repairs as $repair): ?>
        <tr>
          <td><?= $repair->client->name ?></td>
          <td><?= $repair->description ?></td>
          <td>Bs. <?= number_format((float) $repair->price_ves, 2, ',', '.') ?></td>
          <td><?= $repair->due_date->format('d/m/Y') ?></td>
          <td>Bs. <?= number_format($repair->getTotalPaidVes(), 2, ',', '.') ?></td>
          <td>Bs. <?= number_format($repair->getRemainingAmountVes(), 2, ',', '.') ?></td>
          <td>
            <a
              href="./reparaciones/<?= $repair->id ?>"
              target="_blank"
              class="btn btn-secondary w-100">
              Ver comprobante
            </a>
          </td>
        </tr>
        <?php if ($repair->getRemainingAmountVes() > 0): ?>
          <tr>
            <td colspan="4"></td>
            <td>
              <input
                form="pay-repair-<?= $repair->id ?>"
                name="amount_ves"
                type="number"
                min="0.01"
                max="<?= $repair->getRemainingAmountVes() ?>"
                step="0.01"
                required
                class="form-control"
                placeholder="Monto Bs.">
            </td>
            <td>
              <select form="pay-repair-<?= $repair->id ?>" name="method" required class="form-select">
                <option value="" selected disabled>Método</option>
                <option>Físico</option>
                <option>Punto</option>
                <option>Transferencia</option>
              </select>
            </td>
            <td>
              <button form="pay-repair-<?= $repair->id ?>" class="btn btn-primary w-100">Pagar</button>
            </td>
          </tr>
        <?php endif ?>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td>
          <select form="register-repair" name="client_id" required class="form-select">
            <option value="" selected disabled>Cliente</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?= $client->id ?>"><?= $client->name ?></option>
            <?php endforeach ?>
          </select>
        </td>
        <td>
          <input
            form="register-repair"
            name="description"
            required
            class="form-control"
            placeholder="Descripción de la reparación">
        </td>
        <td>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input
              form="register-repair"
              name="price"
              type="number"
              min="1"
              required
              class="form-control"
              placeholder="Precio">
          </div>
        </td>
        <td colspan="4">
          <button form="register-repair" class="btn btn-primary w-100">
            <span class="bi bi-plus-lg"></span>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
