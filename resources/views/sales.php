<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Sale> $sales
 * @var Collection<int, Client> $clients
 * @var Collection<int, Product> $products
 * @var Collection<int, Business> $businesses
 */

?>

<form method="post" id="sell"></form>
<?php foreach ($sales as $sale): ?>
  <form
    method="post"
    id="pay-sale-<?= $sale->id ?>"
    action="./ventas/<?= $sale->id ?>/pagar"></form>
<?php endforeach ?>

<div class="table-responsive">
  <table class="table table-hover table-borderless caption-top align-middle">
    <caption>Lista de ventas</caption>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Cliente</th>
        <th colspan="4">Productos</th>
        <th colspan="4">Pagos</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sales as $sale): ?>
        <tr>
          <td><?= $sale->created_at->format('d/m/Y') ?></td>
          <td><?= $sale->client->name ?></td>
          <td colspan="4">
            <table class="table table-hover table-borderless m-0 align-middle">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Precio</th>
                  <th>Cantidad</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sale->items as $item): ?>
                  <tr>
                    <td><?= $item->product->name ?></td>
                    <td>
                      <span class="bi bi-currency-dollar"></span>
                      <?= $item->price ?>
                    </td>
                    <td><?= $item->quantity ?></td>
                    <td>
                      <span class="bi bi-currency-dollar"></span>
                      <?= $item->getTotal() ?>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">Total</th>
                  <td>
                    <span class="bi bi-currency-dollar"></span>
                    <?= $sale->getTotal() ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </td>
          <td colspan="4">
            <table class="table table-hover table-borderless m-0 align-middle">
              <thead>
                <tr>
                  <th>Método</th>
                  <th colspan="2">Monto</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sale->payments as $payment): ?>
                  <tr>
                    <td><?= $payment->method ?></td>
                    <td colspan="2">
                      <span class="bi bi-currency-dollar"></span>
                      <?= $payment->amount ?>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
              <tfoot>
                <tr>
                  <th class="text-end">Total</th>
                  <td colspan="2">
                    <span class="bi bi-currency-dollar"></span>
                    <?= $sale->getTotalPaid() ?>
                  </td>
                </tr>
                <?php if ($sale->getRemainingAmount()): ?>
                  <tr>
                    <td>
                      <div class="input-group">
                        <span class="input-group-text bi bi-currency-dollar"></span>
                        <input
                          form="pay-sale-<?= $sale->id ?>"
                          type="number"
                          name="amount"
                          required
                          placeholder="Monto"
                          min="0"
                          max="<?= $sale->getRemainingAmount() ?>"
                          class="form-control">
                      </div>
                    </td>
                    <td>
                      <select form="pay-sale-<?= $sale->id ?>" name="method" required class="form-select">
                        <option value="" selected disabled>Método</option>
                        <option>Físico</option>
                        <option>Punto</option>
                        <option>Transferencia</option>
                      </select>
                    </td>
                    <td>
                      <input
                        form="pay-sale-<?= $sale->id ?>"
                        type="submit"
                        value="Pagar"
                        class="btn btn-primary w-100">
                    </td>
                  </tr>
                <?php endif ?>
              </tfoot>
            </table>
          </td>
        </tr>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">
          <select form="sell" name="client_id" required class="form-select">
            <option value="" selected disabled>Cliente</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?= $client->id ?>"><?= $client->name ?></option>
            <?php endforeach ?>
          </select>
        </td>
        <td colspan="4">
          <table class="table table-hover table-borderless p-0">
            <tbody>
              <tr>
                <td>
                  <select
                    form="sell"
                    name="product_id[]"
                    required
                    class="form-select">
                    <option value="" selected disabled>Producto</option>
                    <?php foreach ($products as $product): ?>
                      <option value="<?= $product->id ?>">
                        <?= $product->name ?>
                      </option>
                    <?php endforeach ?>
                  </select>
                </td>
                <td>
                  <select
                    form="sell"
                    name="business_id[]"
                    required
                    class="form-select">
                    <option value="" selected disabled>Depósito</option>
                    <?php foreach ($businesses as $business): ?>
                      <option value="<?= $business->id ?>"><?= $business->name ?></option>
                    <?php endforeach ?>
                  </select>
                </td>
                <td>
                  <?php Flight::render('components/form-control', [
                    'form' => 'sell',
                    'type' => 'number',
                    'name' => 'quantity[]',
                    'required' => true,
                    'min' => 0,
                    'placeholder' => 'Cantidad',
                  ]) ?>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3">
                  <button
                    class="btn btn-secondary w-100"
                    onclick="
                      var table = this.closest('table');
                      var row = table.firstElementChild.lastElementChild;
                      var newRow = row.cloneNode(true);
                      newRow.querySelectorAll('input, select').forEach(input => input.value = '');
                      row.after(newRow);
                    ">
                    <span class="bi bi-plus-lg"></span>
                  </button>
                </td>
              </tr>
            </tfoot>
          </table>
        </td>
        <td>
          <table class="table table-hover table-borderless m-0">
            <tbody>
              <tr>
                <td>
                  <div class="input-group">
                    <span class="input-group-text bi bi-currency-dollar"></span>
                    <input
                      form="sell"
                      type="number"
                      name="amount[]"
                      required
                      min="0"
                      class="form-control"
                      placeholder="Monto">
                  </div>
                </td>
                <td>
                  <select form="sell" name="method[]" required class="form-select">
                    <option value="" selected disabled>Método</option>
                    <option>Físico</option>
                    <option>Punto</option>
                    <option>Transferencia</option>
                  </select>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2">
                  <button
                    class="btn btn-secondary w-100"
                    onclick="
                      var table = this.closest('table');
                      var row = table.firstElementChild.lastElementChild;
                      var newRow = row.cloneNode(true);
                      newRow.querySelectorAll('input, select').forEach(input => input.value = '');
                      row.after(newRow);
                    ">
                    <span class="bi bi-plus-lg"></span>
                  </button>
                </td>
              </tr>
            </tfoot>
          </table>
        </td>
        <td>
          <input form="sell" type="submit" value="Facturar" class="btn btn-primary w-100">
        </td>
      </tr>
    </tfoot>
  </table>
</div>
