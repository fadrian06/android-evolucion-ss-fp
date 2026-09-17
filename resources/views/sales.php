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
 * @var null|string $invoiceId
 */

?>

<?php if ($invoiceId): ?>
  <?php foreach (explode(',', $invoiceId) as $id): ?>
    <script>open('./ventas/<?= $id ?>', '_blank');</script>
  <?php endforeach ?>
<?php endif ?>

<form method="post" id="sell"></form>
<?php foreach ($sales as $sale): ?>
  <form
    method="post"
    id="pay-sale-<?= $sale->id ?>"
    action="./ventas/<?= $sale->id ?>/pagar"></form>
  <form
    method="post"
    id="cancel-sale-<?= $sale->id ?>"
    action="./ventas/<?= $sale->id ?>/anular"></form>
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
        <th>Factura</th>
        <th>Estado</th>
        <th>Acciones</th>
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
                <?php if ($sale->getRemainingAmount() > 0 && !$sale->cancelled_at): ?>
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
                          min="1"
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
          <td>
            <a
              href="./ventas/<?= $sale->id ?>"
              target="_blank"
              class="btn btn-secondary w-100">
              Ver factura
            </a>
          </td>
          <td>
            <?php if ($sale->cancelled_at): ?>
              <span class="badge text-bg-danger">Anulada</span>
            <?php else: ?>
              <span class="badge text-bg-success">Activa</span>
            <?php endif ?>
          </td>
          <td>
            <?php if (!$sale->cancelled_at): ?>
              <button
                form="cancel-sale-<?= $sale->id ?>"
                onclick="return confirm('¿Anular esta factura y restaurar el stock?')"
                class="btn btn-danger w-100">
                Anular
              </button>
            <?php endif ?>
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
                    class="form-select"
                    onchange="
                      var row = this.closest('tr');
                      var category = this.selectedOptions[0].dataset.category;
                      row.querySelectorAll('.phone-identifier').forEach(input => {
                        input.required = category === 'phone';
                      });
                      row.querySelectorAll('.accessory-code').forEach(input => {
                        input.required = category === 'accessory';
                      });
                      row.querySelector('.phone-identifiers').classList.toggle('d-none', category !== 'phone');
                      row.querySelector('.accessory-code-wrapper').classList.toggle('d-none', category !== 'accessory');
                      var hasPhone = document.querySelectorAll('option[data-category=phone]:checked').length > 0;
                      var initialPayments = document.getElementById('initial-payments');
                      initialPayments.classList.toggle('d-none', hasPhone);
                      initialPayments.querySelectorAll('input, select').forEach(input => input.disabled = hasPhone);
                      document.getElementById('add-payment').disabled = hasPhone;
                    ">
                    <option value="" selected disabled>Producto</option>
                    <?php foreach ($products as $product): ?>
                      <option value="<?= $product->id ?>" data-category="<?= $product->category ?>">
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
                <td>
                  <div class="phone-identifiers d-none">
                    <input
                      form="sell"
                      type="text"
                      name="imei1[]"
                      class="form-control phone-identifier mb-2"
                      placeholder="IMEI 1">
                    <input
                      form="sell"
                      type="text"
                      name="imei2[]"
                      class="form-control phone-identifier"
                      placeholder="IMEI 2">
                  </div>
                  <div class="accessory-code-wrapper d-none">
                    <input
                      form="sell"
                      type="text"
                      name="code[]"
                      class="form-control accessory-code"
                      placeholder="Código">
                  </div>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4">
                  <button
                    class="btn btn-secondary w-100"
                    onclick="
                      var table = this.closest('table');
                      var row = table.firstElementChild.lastElementChild;
                      var newRow = row.cloneNode(true);
                      newRow.querySelectorAll('input, select').forEach(input => input.value = '');
                      newRow.querySelectorAll('.phone-identifier, .accessory-code').forEach(input => input.required = false);
                      newRow.querySelectorAll('.phone-identifiers, .accessory-code-wrapper').forEach(element => {
                        element.classList.add('d-none');
                      });
                      row.after(newRow);
                    ">
                    <span class="bi bi-plus-lg"></span>
                  </button>
                </td>
              </tr>
            </tfoot>
          </table>
        </td>
        <td id="initial-payments">
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
                    id="add-payment"
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
