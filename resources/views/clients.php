<?php

declare(strict_types=1);

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

/** @var Collection<int, Client> $clients */

?>

<form method="post" id="register-client"></form>
<?php foreach ($clients as $client): ?>
  <form
    method="post"
    id="update-client-<?= $client->id ?>"
    action="./clientes/<?= $client->id ?>"></form>
<?php endforeach ?>

<div class="table-responsive">
  <table
    class="table table-hover table-borderless caption-top"
    x-data='{
      clients: JSON.parse(`<?= $clients->toJson() ?>`),

      clientExists(name, excludedId) {
        return this.clients.some(client => {
          if (client.id === excludedId) {
            return false;
          }

          return client.name.toLowerCase() === name.toLowerCase();
        });
      },
    }'>
    <caption>Lista de clientes</caption>
    <thead>
      <tr>
        <th colspan="3">Nombre</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($clients as $client): ?>
        <tr>
          <td>
            <input
              form="update-client-<?= $client->id ?>"
              name="name"
              value="<?= $client->name ?>"
              required
              class="form-control"
              :class="{ 'is-valid': isValid, 'is-invalid': isValid === false }"
              x-data="{ isValid: undefined }"
              @input="
                isValid = $el.value
                  ? ($el.checkValidity() && !clientExists($el.value, <?= $client->id ?>))
                  : undefined
              ">
          </td>
          <td>
            <input
              form="update-client-<?= $client->id ?>"
              type="submit"
              value="Actualizar"
              class="btn btn-primary w-100">
          </td>
          <td>
            <a href="./clientes/<?= $client->id ?>/eliminar" class="btn btn-danger w-100">
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
            form="register-client"
            name="name"
            required
            class="form-control"
            :class="{ 'is-valid': isValid, 'is-invalid': isValid === false }"
            x-data="{ isValid: undefined }"
            @input="
              isValid = $el.value
                ? ($el.checkValidity() && !clientExists($el.value))
                : undefined
            ">
        </td>
        <td colspan="2">
          <button form="register-client" class="btn btn-primary w-100">
            <span class="bi bi-plus-lg"></span>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
