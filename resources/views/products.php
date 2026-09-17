<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Product> $products
 * @var Collection<int, Business> $businesses
 */

?>

<form method="post" id="add-product"></form>
<?php foreach ($products as $product): ?>
  <form
    method="post"
    id="update-product-<?= $product->id ?>"
    action="./productos/<?= $product->id ?>"></form>
<?php endforeach ?>

<div class="table-responsive">
  <table
    class="table table-hover table-borderless caption-top align-middle"
    x-data='{
      products: JSON.parse(`<?= $products->toJson() ?>`),

      productExists(name, excludedId) {
        return this.products.some(product => {
          if (product.id === excludedId) {
            return false;
          }

          return product.name.toLowerCase() === name.toLowerCase();
        });
      },
    }'>
    <caption>Lista de productos</caption>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Precio</th>
        <th colspan="3">Unidades disponibles</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
        <tr>
          <td>
            <?php Flight::render('components/form-control', [
              'form' => "update-product-$product->id",
              'name' => 'name',
              'value' => $product->name,
              'required' => true,
              'onInput' => "
                isValid = \$el.value
                  ? (\$el.checkValidity() && !productExists(\$el.value, $product->id))
                  : undefined
              ",
            ]) ?>
          </td>
          <td>
            <select
              form="update-product-<?= $product->id ?>"
              name="category"
              required
              class="form-select">
              <option value="phone" <?= $product->category === 'phone' ? 'selected' : '' ?>>Teléfono</option>
              <option value="accessory" <?= $product->category === 'accessory' ? 'selected' : '' ?>>Accesorio</option>
            </select>
          </td>
          <td>
            <?php Flight::render('components/input-group', [
              'form' => "update-product-$product->id",
              'type' => 'number',
              'name' => 'price',
              'value' => $product->price,
              'required' => true,
              'min' => 0,
              'icon' => 'bi bi-currency-dollar',
            ]) ?>
          </td>
          <td>
            <table class="table table-hover table-borderless m-0 align-middle">
              <tbody>
                <?php foreach ($businesses as $business): ?>
                  <?php $batch = $product->batches->first(
                    static fn (Batch $batch): bool => $batch->business->id === $business->id
                  ) ?>
                  <tr>
                    <td><?= $business->name ?></td>
                    <td>
                      <?php Flight::render('components/form-control', [
                        'form' => "update-product-$product->id",
                        'type' => 'number',
                        'name' => "stocks[$business->id]",
                        'value' => $batch?->stock ?? 0,
                        'required' => true,
                        'min' => 0,
                      ]) ?>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
              <tfoot>
                <tr>
                  <th class="text-end">Total</th>
                  <td>
                    <input
                      type="number"
                      disabled
                      class="form-control"
                      value="<?= $product->getStock() ?>">
                  </td>
                </tr>
              </tfoot>
            </table>
          </td>
          <td>
            <input
              form="update-product-<?= $product->id ?>"
              type="submit"
              class="btn btn-primary w-100"
              value="Actualizar">
          </td>
          <td>
            <a href="./productos/<?= $product->id ?>/eliminar" class="btn btn-danger w-100">
              <span class="bi bi-trash"></span>
            </a>
          </td>
        </tr>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td>
          <input
            form="add-product"
            name="name"
            required
            class="form-control"
            placeholder="Nombre"
            :class="{ 'is-valid': isValid, 'is-invalid': isValid === false }"
            x-data="{ isValid: undefined }"
            @input="
              isValid = $el.value
                ? ($el.checkValidity() && !productExists($el.value))
                : undefined
            ">
        </td>
        <td>
          <select form="add-product" name="category" required class="form-select">
            <option value="" selected disabled>Categoría</option>
            <option value="phone">Teléfono</option>
            <option value="accessory">Accesorio</option>
          </select>
        </td>
        <td>
          <?php Flight::render('components/input-group', [
            'icon' => 'bi bi-currency-dollar',
            'form' => 'add-product',
            'type' => 'number',
            'name' => 'price',
            'required' => true,
            'min' => 0,
            'placeholder' => 'Precio',
          ]) ?>
        </td>
        <td>
          <table class="table table-hover table-borderless m-0 align-middle">
            <tbody>
              <?php foreach ($businesses as $business): ?>
                <tr>
                  <td><?= $business->name ?></td>
                  <td>
                    <?php Flight::render('components/form-control', [
                      'form' => 'add-product',
                      'type' => 'number',
                      'name' => "stocks[$business->id]",
                      'required' => true,
                      'min' => 0,
                      'placeholder' => 'Unidades',
                    ]) ?>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </td>
        <td colspan="2">
          <button form="add-product" class="btn btn-primary w-100">
            <span class="bi bi-plus-lg"></span>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
