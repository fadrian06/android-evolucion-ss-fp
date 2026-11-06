<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\ExchangeRate;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Product> $products
 * @var Collection<int, Business> $businesses
 * @var ExchangeRate $customRate
 */

$labels = [
  'phone' => 'Teléfono',
  'accessory' => 'Accesorio',
  'spare_part' => 'Repuesto',
];

?>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'INVENTARIO',
  'title' => 'Productos',
  'description' => 'Precios y existencias por local.',
  'buttonText' => '+ Añadir producto',
  'buttonAction' => '$refs.new?.showModal()',
]) ?>

<dialog class="card card-medium" x-ref="new">
  <form class="panel stack" method="post" action="./productos">
    <?php Flight::render('components/toolbar', [
      'title' => 'Añadir producto',
      'inDialog' => true,
    ]) ?>

    <div class="grid grid-3">
      <div class="field">
        <label>Nombre</label>
        <input name="name" required placeholder="Nombre del producto">
      </div>

      <div class="field">
        <label>Categoría</label>
        <select name="category" required>
          <option value="phone">Teléfono</option>
          <option value="accessory">Accesorio</option>
          <option value="spare_part">Repuesto</option>
        </select>
      </div>

      <div class="field">
        <label>Precio USD</label>
        <input
          name="price"
          type="number"
          data-currency="usd"
          min=".01"
          step=".01"
          required
          placeholder="0.00">
      </div>
    </div>

    <div class="grid grid-3">
      <?php foreach ($businesses as $businessItem): ?>
        <div class="field">
          <label>Stock · <?= $businessItem->name ?></label>
          <input
            name="stocks[<?= $businessItem->id ?>]"
            type="number"
            min="0"
            required
            value="0">
        </div>
      <?php endforeach ?>
    </div>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Guardar producto',
        'type' => 'submit',
      ]) ?>
    </div>
  </form>
</dialog>

<div class="d-contents" x-data="{
  name: '',
  category: '',
  disponibility: '',

  matchName(name = '') {
    return trim(name).includes(trim(this.name));
  },

  matchCategory(category = '') {
    return !this.category || category === this.category;
  },

  matchStock(stock = 0) {
    if (this.disponibility === 'low-stock') {
      return stock === 1;
    }

    if (this.disponibility === 'out-of-stock') {
      return !stock;
    }

    return true;
  },
}">
  <section class="card panel stack">
    <div class="grid grid-3">
      <div class="field">
        <label>Buscar productos</label>
        <input
          x-model="name"
          type="search"
          placeholder="Nombre del producto">
      </div>

      <div class="field">
        <label>Categoría</label>
        <select x-model="category">
          <option value="">Todas las categorías</option>
          <option value="phone">Teléfonos</option>
          <option value="accessory">Accesorios</option>
          <option value="spare_part">Repuestos</option>
        </select>
      </div>

      <div class="field">
        <label>Disponibilidad</label>
        <select x-model="disponibility">
          <option value="">Todo el inventario</option>
          <option value="low-stock">Casi agotados</option>
          <option value="out-of-stock">Agotados</option>
        </select>
      </div>
    </div>
  </section>

  <section class="card table-wrap">
    <?php Flight::render('components/toolbar', [
      'title' => "{$products->count()} productos",
    ]) ?>

    <table class="data-table">
      <thead>
        <tr>
          <th>Imagen</th>
          <th>Producto</th>
          <th>Categoría</th>
          <th>Precio</th>
          <th>Stock total</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($products as $product): ?>
          <tr
            x-show="
              matchName('<?= $product->name ?>')
              && matchCategory('<?= $product->category ?>')
              && matchStock(<?= $product->getStock() ?>)
            "
            x-transition>
            <td>
              <img
                src="./resources/images/product-placeholder.svg"
                width="44"
                height="44"
                class="product-placeholder">
            </td>
            <td>
              <strong><?= $product->name ?></strong>
            </td>
            <td class="product-category"><?= $labels[$product->category] ?></td>
            <td class="product-price">
              <abbr :data-title="`Bs. ${getPriceInVES(<?= $product->price ?>).toLocaleString('es-VE')}`">
                $<?= number_format($product->price, 2, ',', '.') ?>
              </abrr>
            </td>
            <td class="stock-total">
              <?php if (!$product->getStock()): ?>
                <?php Flight::render('components/status', [
                  'variant' => 'danger',
                  'text' => 'Agotado',
                ]) ?>
              <?php elseif ($product->getStock() === 1): ?>
                <?php Flight::render('components/status', [
                  'variant' => 'warning',
                  'text' => 'Casi agotado',
                  'title' => 'Este producto tiene solo 1 unidad en stock. Considera reponerlo pronto.',
                ]) ?>
              <?php else: ?>
                <?= $product->getStock() ?> unidades
              <?php endif ?>
            </td>
            <td>
              <div class="actions">
                <?php Flight::render('components/btn', [
                  'text' => 'Editar',
                  'variant' => 'secondary',
                  'small' => true,
                  'onclick' => "\$refs.edit$product->id?.showModal()",
                ]) ?>

                <?php Flight::render('components/btn', [
                  'text' => 'Eliminar',
                  'variant' => 'danger',
                  'small' => true,
                  'href' => "./productos/$product->id/eliminar",
                  'disabled' => !$product->canBeDeleted(),
                  'title' => 'No se puede eliminar un producto con registros asociados',
                ]) ?>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </section>
</div>

<?php foreach ($products as $product): ?>
  <dialog x-ref="edit<?= $product->id ?>" class="card card-medium">
    <form
      method="post"
      action="./productos/<?= $product->id ?>"
      class="panel stack">
      <?php Flight::render('components/toolbar', [
        'title' => 'Editar producto',
        'inDialog' => true,
      ]) ?>

      <div class="grid grid-3">
        <div class="field">
          <label>Nombre</label>
          <input name="name" value="<?= $product->name ?>" required>
        </div>

        <div class="field">
          <label>Categoría</label>
          <select name="category">
            <option
              value="phone"
              <?= $product->category === 'phone' ? 'selected' : '' ?>>
              Teléfono
            </option>
            <option
              value="accessory"
              <?= $product->category === 'accessory' ? 'selected' : '' ?>>
              Accesorio
            </option>
            <option
              value="spare_part"
              <?= $product->category === 'spare_part' ? 'selected' : '' ?>>
              Repuesto
            </option>
          </select>
        </div>

        <div class="field">
          <label>Precio USD</label>
          <input
            name="price"
            type="number"
            data-currency="usd"
            min=".01"
            step=".01"
            value="<?= number_format($product->price, 2, '.', '') ?>"
            required>
        </div>
      </div>

      <div class="grid grid-3">
        <?php foreach ($businesses as $businessItem): ?>
          <div class="field">
            <label>Stock · <?= $businessItem->name ?></label>
            <input
              name="stocks[<?= $businessItem->id ?>]"
              type="number"
              min="0"
              value="<?= $product->getStockInBusiness($businessItem) ?>"
              required>
          </div>
        <?php endforeach ?>
      </div>

      <div class="actions">
        <?php Flight::render('components/btn', [
          'text' => 'Guardar',
          'type' => 'submit',
        ]) ?>
      </div>
    </form>
  </dialog>
<?php endforeach ?>
