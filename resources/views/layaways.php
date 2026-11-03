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

<style>
  .layaway-payment-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(190px, 1fr) auto;
    gap: 10px;
    align-items: end
  }

  @media (max-width: 720px) {
    .layaway-payment-row {
      grid-template-columns: 1fr;
    }
  }
</style>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'RESERVAS',
  'title' => 'Apartados',
  'description' => 'Productos reservados, pagos parciales y stock protegido.',
  'buttonText' => '+ Nuevo apartado',
  'buttonAction' => '$refs.new?.showModal()',
]) ?>

<dialog class="card card-medium" x-ref="new">
  <form class="panel stack" method="post" action="./apartados">
    <?php Flight::render('components/toolbar', [
      'title' => 'Nuevo apartado',
      'inDialog' => true,
    ]) ?>

    <div class="grid grid-2">
      <div class="field">
        <label>Cliente</label>

        <select name="client_id" required>
          <option value="" hidden>Seleccionar cliente</option>

          <?php foreach ($clients as $client): ?>
            <option value="<?= $client->id ?>"><?= $client->name ?></option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="field">
        <label>Producto disponible</label>
        <select name="product_id" required id="layaway-product">
          <option value="" hidden>Seleccionar producto</option>

          <?php foreach ($products as $product): ?>
            <option
              value="<?= $product->id ?>"
              data-category="<?= $product->category ?>">
              <?= $product->name ?> · $<?= $product->price ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>
    </div>

    <div class="grid grid-2 hidden" id="layaway-imeis">
      <div class="field">
        <label>IMEI 1</label>
        <input name="imei1" placeholder="IMEI 1">
      </div>

      <div class="field">
        <label>IMEI 2</label>
        <input name="imei2" placeholder="IMEI 2">
      </div>
    </div>

    <div class="field hidden" id="layaway-code">
      <label>
        Código
        <small class="muted-text">(opcional)</small>
      </label>
      <input name="code" placeholder="Código del accesorio">
    </div>

    <section class="card panel stack">
      <div>
        <h2 class="card-title">
          Pagos iniciales
          <small class="muted-text">(opcionales)</small>
        </h2>

        <p class="muted">
          Efectivo USD registra dólares; los demás métodos registran Bs. con
          su cotización histórica.
        </p>
      </div>

      <div id="layaway-initial-payments" class="stack">
        <div class="layaway-payment-row">
          <div class="field">
            <label>Monto recibido</label>
            <input
              name="amount[]"
              type="number"
              min=".01"
              step=".01"
              placeholder="Sin pago inicial">
          </div>

          <div class="field">
            <label>Método</label>
            <select name="method[]">
              <option value="Efectivo USD">Efectivo USD</option>
              <option value="Efectivo Bs.">Efectivo Bs.</option>
              <option value="Punto">Punto</option>
              <option value="Transferencia">Transferencia</option>
            </select>
          </div>
        </div>
      </div>

      <?php Flight::render('components/btn', [
        'text' => '+ Añadir pago',
        'variant' => 'secondary',
        'small' => true,
        'id' => 'add-layaway-payment',
      ]) ?>
    </section>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Confirmar apartado',
        'type' => 'submit',
      ]) ?>
    </div>
  </form>
</dialog>

<div class="d-contents">
  <section class="card panel stack">
    <div class="grid grid-2">
      <div class="field">
        <label>Buscar</label>
        <input
          id="layaway-search"
          type="search"
          placeholder="Cliente, producto, IMEI o código">
      </div>

      <div class="field">
        <label>Estado</label>
        <select id="layaway-status">
          <option value="">Todos</option>
          <option value="pending">Pendientes</option>
          <option value="paid">Pagados</option>
          <option value="cancelled">Cancelados</option>
        </select>
      </div>
    </div>
  </section>

  <section class="card table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Producto</th>
          <th>Identificador</th>
          <th>Total</th>
          <th>Pagado</th>
          <th>Pago pendiente</th>
        </tr>
      </thead>
      <tbody id="layaway-list">
        <?php foreach ($layaways as $item):
          $remainingAmount = $item->getRemainingAmount(); ?>

          <tr data-status="<?= $item->getStatus() ?>">
            <td><?= $item->client->name ?></td>
            <td>
              <strong><?= $item->product->name ?></strong>
            </td>
            <td>
              <small>
                <?php if ($item->product->isPhone()): ?>
                  <?= $item->imei1 ?>
                  <br>
                  <?= $item->imei2 ?>
                <?php else: ?>
                  <?= $item->code ?: 'Sin código' ?>
                <?php endif ?>
              </small>
            </td>
            <td>$<?= number_format($item->price, 2, ',', '.') ?></td>
            <td>
              $<?= number_format($item->getTotalPaid(), 2, ',', '.') ?>
            </td>
            <td>
              <?php if ($item->isFullyPaid()): ?>
                <?php Flight::render('components/status', [
                  'variant' => 'success',
                  'text' => 'Pagado',
                ]) ?>
              <?php else: ?>
                <strong>
                  $<?= number_format($remainingAmount, 2, ',', '.') ?>
                </strong>
              <?php endif ?>
            </td>
            <td>
              <div class="actions">
                <?php if ($item->isCancelled()): ?>
                  <?php Flight::render('components/status', [
                    'variant' => 'danger',
                    'text' => 'Cancelado',
                  ]) ?>
                <?php elseif ($item->canBePaid()): ?>
                  <?php Flight::render('components/btn', [
                    'text' => 'Registrar pago',
                    'small' => true,
                    'onclick' => "\$refs.pay$item->id?.showModal()",
                  ]) ?>

                  <form
                    method="post"
                    action="./apartados/<?= $item->id ?>/cancelar">
                    <?php Flight::render('components/btn', [
                      'text' => 'Cancelar',
                      'variant' => 'danger',
                      'small' => true,
                      'type' => 'submit',
                      'onclick' => '
                        if (!confirm(`¿Cancelar apartado y restaurar el stock?`)) {
                          $event.preventDefault();
                        }
                      ',
                    ]) ?>
                  </form>
                <?php endif ?>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </section>
</div>

<?php foreach ($layaways as $item): ?>
  <?php if ($item->canBePaid()): ?>
    <dialog class="card card-medium" x-ref="pay<?= $item->id ?>">
      <form
        class="panel stack"
        method="post"
        action="./apartados/<?= $item->id ?>/pagar">
        <?php Flight::render('components/toolbar', [
          'title' => 'Registrar pago',
          'inDialog' => true,
        ]) ?>

        <p class="muted-text">
          Pago pendiente:
          <strong>
            $<?= number_format($item->getRemainingAmount(), 2, ',', '.') ?>
          </strong>
        </p>

        <div class="grid grid-2">
          <div class="field">
            <label>Monto recibido</label>
            <input
              name="amount"
              type="number"
              min=".01"
              step=".01"
              required
              placeholder="0.00">
          </div>

          <div class="field">
            <label>Método</label>
            <select name="method" class="layaway-payment-method" required>
              <option value="Efectivo USD">Efectivo USD</option>
              <option value="Efectivo Bs.">Efectivo Bs.</option>
              <option value="Punto">Punto</option>
              <option value="Transferencia">Transferencia</option>
            </select>
          </div>
        </div>

        <p class="muted-text">
          Los pagos en Bs. guardan la cotización personalizada vigente como
          referencia histórica.
        </p>

        <div class="actions">
          <?php Flight::render('components/btn', [
            'text' => 'Guardar pago',
            'type' => 'submit',
          ]) ?>
        </div>
      </form>
    </dialog>
  <?php endif ?>
<?php endforeach ?>

<script>
  const layawayProduct = document.querySelector('#layaway-product'),
    layawayImeis = document.querySelector('#layaway-imeis'),
    layawayCode = document.querySelector('#layaway-code'),
    imeiInputs = layawayImeis.querySelectorAll('input');

  function updateLayawayIdentifiers() {
    const category = layawayProduct.selectedOptions[0]?.dataset.category,
      isPhone = category === 'phone',
      isAccessory = category === 'accessory';

    layawayImeis.classList.toggle('hidden', !isPhone);
    layawayCode.classList.toggle('hidden', !isAccessory);

    imeiInputs.forEach(input => {
      input.required = isPhone;

      if (!isPhone) input.value = ''
    });

    if (!isAccessory) layawayCode.querySelector('input').value = ''
  }

  layawayProduct.addEventListener('change', updateLayawayIdentifiers);

  const layawayPayments = document.querySelector('#layaway-initial-payments'),
    layawayPaymentTemplate = layawayPayments.firstElementChild.outerHTML;

  document.querySelector('#add-layaway-payment').onclick = () => {
    layawayPayments.insertAdjacentHTML('beforeend', layawayPaymentTemplate);

    layawayPayments.lastElementChild.insertAdjacentHTML(
      'beforeend',
      `<?php Flight::render('components/btn', [
        'type' => 'button',
        'variant' => 'danger',
        'small' => true,
        'class' => 'remove-payment',
        'text' => 'Quitar pago',
      ]); ?>`
    )
  };

  layawayPayments.addEventListener('click', event => {
    if (event.target.closest('.remove-payment')) event.target.closest('.layaway-payment-row').remove()
  });

  const layawaySearch = document.querySelector('#layaway-search'),
    layawayStatus = document.querySelector('#layaway-status'),
    layawayRows = document.querySelectorAll('#layaway-list tr');

  function filterLayaways() {
    const query = layawaySearch.value.toLowerCase();

    layawayRows.forEach(row => row.hidden = !(row.textContent.toLowerCase().includes(query) && (!layawayStatus.value || row.dataset.status === layawayStatus.value)))
  }

  layawaySearch.addEventListener('input', filterLayaways);
  layawayStatus.addEventListener('change', filterLayaways);
</script>
