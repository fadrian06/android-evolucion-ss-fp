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

<?php Flight::render('components/page-head', [
  'eyebrow' => 'RESERVAS',
  'title' => 'Apartados',
  'description' => 'Productos reservados, pagos parciales y stock protegido.',
  'buttonText' => '+ Nuevo apartado',
  'buttonAction' => '$refs.new?.showModal()',
]) ?>

<dialog class="card card-medium" x-ref="new">
  <form class="panel stack" method="post" action="./apartados" x-data='{
    products: Object.values(<?= $products->toJson() ?>),
    productId: "",
    payments: [{ id: crypto.randomUUID(), amount: "", method: "" }],

    get product() {
      for (const product of this.products) {
        if (product.id === this.productId) {
          return product;
        }
      }

      return null;
    },
  }'>
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
        <select name="product_id" required x-model="productId">
          <option value="" hidden>Seleccionar producto</option>

          <?php foreach ($products as $product): ?>
            <option value="<?= $product->id ?>">
              <?= $product->name ?> · $<?= $product->price ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>
    </div>

    <div
      class="grid grid-2"
      x-show="product && product.category === 'phone'"
      x-transition:enter>
      <div class="field">
        <label>IMEI 1</label>
        <input name="imei1" placeholder="IMEI 1">
      </div>

      <div class="field">
        <label>IMEI 2</label>
        <input name="imei2" placeholder="IMEI 2">
      </div>
    </div>

    <div
      class="field"
      x-show="product && product.category !== 'phone'"
      x-transition:enter>
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

      <p class="muted-text" x-show="product" x-transition>
        Pago pendiente:
        <strong
          x-text="(product?.price - getTotalPaid(payments)).toLocaleString('es-VE', {
            style: 'currency',
            currency: 'USD',
            currencyDisplay: 'narrowSymbol',
          })">
        </strong>
      </p>

      <div class="stack">
        <template x-for="(payment, index) in payments" :key="payment.id">
          <div
            class="grid grid-3"
            x-data="{ visible: false }"
            x-init="$nextTick(() => visible = true)"
            x-show="visible"
            x-transition.duration.200ms>
            <div class="field">
              <label>Monto recibido</label>
              <input
                x-model="payment.amount"
                name="amount[]"
                type="number"
                min=".01"
                step=".01"
                placeholder="Sin pago inicial">
            </div>

            <div class="field">
              <label>Método</label>
              <select name="method[]" x-model="payment.method">
                <option value="Efectivo USD">Efectivo USD</option>
                <option value="Efectivo Bs.">Efectivo Bs.</option>
                <option value="Punto">Punto</option>
                <option value="Transferencia">Transferencia</option>
              </select>
            </div>

            <div class="actions" x-show="index">
              <?php Flight::render('components/btn', [
                'type' => 'button',
                'variant' => 'danger',
                'small' => true,
                'text' => 'Quitar pago',
                'onclick' => '
                  visible = false;

                  setTimeout(() => payments.splice(index, 1), 200);
                ',
              ]) ?>
            </div>
        </template>

        <?php Flight::render('components/btn', [
          'text' => '+ Añadir pago',
          'variant' => 'secondary',
          'small' => true,
          'onclick' => 'payments.push({
            id: crypto.randomUUID(),
            amount: ``,
            method: ``,
          })',
        ]) ?>
      </div>
    </section>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Confirmar apartado',
        'type' => 'submit',
      ]) ?>
    </div>
  </form>
</dialog>

<div class="d-contents" x-data="{
  search: '',
  status: '',

  matchSearch($card = new HTMLElement()) {
    return trim($card.textContent).includes(trim(this.search));
  },

  matchStatus(status) {
    return this.status === '' || status === this.status;
  }
}">
  <section class="card panel stack">
    <div class="grid grid-2">
      <div class="field">
        <label>Buscar</label>
        <input
          x-model="search"
          type="search"
          placeholder="Cliente, producto, IMEI o código">
      </div>

      <div class="field">
        <label>Estado</label>
        <select x-model="status">
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
      <tbody>
        <?php foreach ($layaways as $item): ?>
          <tr
            x-show="matchSearch($el) && matchStatus(`<?= $item->getStatus() ?>`)"
            x-transition>
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
                  $<?= number_format($item->getRemainingAmount(), 2, ',', '.') ?>
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
        action="./apartados/<?= $item->id ?>/pagar"
        x-data="{
          payments: [{ id: crypto.randomUUID(), amount: '', method: 'Efectivo USD' }],
        }">
        <?php Flight::render('components/toolbar', [
          'title' => 'Registrar pago',
          'inDialog' => true,
        ]) ?>

        <p class="muted-text">
          Pago pendiente:
          <strong
            x-text="(<?= $item->getRemainingAmount() ?> - getTotalPaid(payments)).toLocaleString('es-VE', {
              style: 'currency',
              currency: 'USD',
              currencyDisplay: 'narrowSymbol',
            })">
          </strong>
        </p>

        <div class="stack">
          <template x-for="(payment, index) in payments" :key="payment.id">
            <div
              class="grid grid-3"
              x-data="{ visible: false }"
              x-init="$nextTick(() => visible = true)"
              x-show="visible"
              x-transition.duration.200ms>
              <div class="field">
                <label>Monto recibido</label>
                <input
                  x-model="payment.amount"
                  name="amount[]"
                  type="number"
                  min=".01"
                  step=".01"
                  placeholder="Sin pago inicial">
              </div>

              <div class="field">
                <label>Método</label>
                <select name="method[]" x-model="payment.method">
                  <option value="Efectivo USD">Efectivo USD</option>
                  <option value="Efectivo Bs.">Efectivo Bs.</option>
                  <option value="Punto">Punto</option>
                  <option value="Transferencia">Transferencia</option>
                </select>
              </div>

              <div class="actions" x-show="index">
                <?php Flight::render('components/btn', [
                  'type' => 'button',
                  'variant' => 'danger',
                  'small' => true,
                  'text' => 'Quitar pago',
                  'onclick' => '
                    visible = false;

                    setTimeout(() => payments.splice(index, 1), 200);
                  ',
                ]) ?>
              </div>
          </template>
        </div>

        <?php Flight::render('components/btn', [
          'text' => '+ Añadir pago',
          'variant' => 'secondary',
          'small' => true,
          'onclick' => 'payments.push({
            id: crypto.randomUUID(),
            amount: ``,
            method: ``,
          })',
        ]) ?>

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
