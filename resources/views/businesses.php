<?php

declare(strict_types=1);

use App\Models\Business;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var unset|Business $business
 * @var Collection<int, Business> $businesses
 */

?>

<form method="post" id="register-business"></form>
<?php foreach ($businesses as $businessItem): ?>
  <form
    method="post"
    id="update-business-<?= $businessItem->id ?>"
    action="./negocios/<?= $businessItem->id ?>"></form>
<?php endforeach ?>

<div class="table-responsive">
  <table
    class="table table-striped table-hover table-borderless caption-top align-middle"
    x-data='{
      businesses: JSON.parse(`<?= $businesses->toJson() ?>`),

      businessExists(name, excludedId) {
        return this.businesses.some(business => {
          if (business.id === excludedId) {
            return false;
          }

          return business.name.toLowerCase() === name.toLowerCase();
        });
      },
    }'>
    <caption>Lista de negocios</caption>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>RIF</th>
        <th>Dirección</th>
        <th>Teléfono</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($businesses as $businessItem): ?>
        <tr>
          <td>
            <input
              form="update-business-<?= $businessItem->id ?>"
              name="name"
              value="<?= $businessItem->name ?>"
              required
              class="form-control"
              :class="{ 'is-valid': isValid, 'is-invalid': isValid === false }"
              x-data="{ isValid: undefined }"
              @input="
                isValid = $el.value
                  ? ($el.checkValidity() && !businessExists($el.value, <?= $businessItem->id ?>))
                  : undefined
              ">
          </td>
          <td>
            <input
              form="update-business-<?= $businessItem->id ?>"
              name="rif"
              value="<?= $businessItem->rif ?>"
              required
              class="form-control">
          </td>
          <td>
            <input
              form="update-business-<?= $businessItem->id ?>"
              name="address"
              value="<?= $businessItem->address ?>"
              required
              class="form-control">
          </td>
          <td>
            <input
              form="update-business-<?= $businessItem->id ?>"
              name="phone"
              value="<?= $businessItem->phone ?>"
              required
              class="form-control">
          </td>
          <td>
            <?php if (isset($business) && $business->id === $businessItem->id): ?>
              <span class="badge text-bg-info w-100">
                Seleccionado
              </span>
            <?php else: ?>
              <a href="./negocios/<?= $businessItem->id ?>/seleccionar" class="btn btn-primary w-100">
                Seleccionar
              </a>
            <?php endif ?>
          </td>
          <td>
            <input
              form="update-business-<?= $businessItem->id ?>"
              type="submit"
              value="Actualizar"
              class="btn btn-secondary w-100">
          </td>
          <td>
            <a href="./negocios/<?= $businessItem->id ?>/eliminar" class="btn btn-danger w-100">
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
            form="register-business"
            name="name"
            required
            class="form-control"
            placeholder="Nombre"
            :class="{ 'is-valid': isValid, 'is-invalid': isValid === false }"
            x-data="{ isValid: undefined }"
            @input="
              isValid = $el.value
                ? ($el.checkValidity() && !businessExists($el.value))
                : undefined
            ">
        </td>
        <td>
          <input
            form="register-business"
            name="rif"
            required
            class="form-control"
            placeholder="RIF">
        </td>
        <td>
          <input
            form="register-business"
            name="address"
            required
            class="form-control"
            placeholder="Dirección">
        </td>
        <td>
          <input
            form="register-business"
            name="phone"
            required
            class="form-control"
            placeholder="Teléfono">
        </td>
        <td colspan="3">
          <button form="register-business" class="btn btn-primary w-100">
            <span class="bi bi-plus-lg"></span>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
