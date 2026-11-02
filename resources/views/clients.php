<?php

declare(strict_types=1);

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

/** @var Collection<int, Client> $clients */

?>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'DIRECTORIO',
  'title' => 'Clientes',
  'description' => 'Información de contacto y ventas.',
  'buttonText' => '+ Nuevo cliente',
  'buttonAction' => '$refs.new?.showModal()',
]) ?>

<dialog class="card card-small" x-ref="new">
  <form class="panel stack" method="post" action="./clientes">
    <?php Flight::render('components/toolbar', [
      'title' => 'Registrar cliente',
      'inDialog' => true,
    ]) ?>

    <div class="field">
      <label>Nombre</label>
      <input name="name" required placeholder="Nombre completo">
    </div>

    <div class="field">
      <label>Cédula</label>
      <input name="id_card" required placeholder="V-00000000">
    </div>

    <div class="field">
      <label>Teléfono</label>
      <input type="phone" name="phone" required placeholder="0412-0000000">
    </div>

    <div class="field">
      <label>Dirección</label>
      <textarea name="address" required placeholder="Dirección"></textarea>
    </div>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Guardar cliente',
        'type' => 'submit',
      ]) ?>
    </div>
  </form>
</dialog>

<div class="d-contents" x-data="{
  search: '',

  matchSearch($card = new HTMLElement()) {
    return $card
      .textContent
      .toLowerCase()
      .replace(/\s+/g, '')
      .includes(this.search.toLowerCase().replace(/\s+/g, ''));
  },
}">
  <section class="card panel field">
    <label>Buscar clientes</label>
    <input
      x-model="search"
      type="search"
      placeholder="Nombre, cédula, teléfono o dirección">
  </section>

  <section class="grid grid-3">
    <?php foreach ($clients as $client): ?>
      <article class="card panel" x-show="matchSearch($el)" x-transition>
        <header class="card-header">
          <div class="card-avatar card-avatar-rounded">
            <?= $client->name[0] . $client->name[1] ?>
          </div>
          <div>
            <?php Flight::render('components/eyebrow', [
              'text' => "CLIENTE #$client->id",
            ]) ?>

            <h2 class="card-title"><?= $client->name ?></h2>
          </div>
        </header>

        <div class="card-body">
          <span>
            <strong class="card-eyebrow">C.I.</strong>
            · <?= $client->id_card ?>
          </span>

          <span><?= $client->phone ?></span>
          <span><?= $client->address ?></span>
        </div>

        <footer class="actions">
          <?php Flight::render('components/btn', [
            'text' => 'Editar',
            'variant' => 'secondary',
            'small' => true,
            'onclick' => "\$refs.edit$client->id?.showModal()",
          ]) ?>

          <?php Flight::render('components/btn', [
            'text' => 'Eliminar',
            'variant' => 'danger',
            'small' => true,
            'href' => "./clientes/$client->id/eliminar",
            'disabled' => !$client->canBeDeleted(),
            'title' => 'No se puede eliminar un cliente con registros asociados',
          ]) ?>
        </footer>
      </article>

      <dialog class="card card-small" x-ref="edit<?= $client->id ?>">
        <form
          class="panel stack"
          method="post"
          action="./clientes/<?= $client->id ?>">
          <?php Flight::render('components/toolbar', [
            'title' => 'Editar cliente',
            'inDialog' => true,
          ]) ?>

          <div class="field">
            <label>Nombre</label>
            <input
              name="name"
              required
              placeholder="Nombre completo"
              value="<?= $client->name ?>">
          </div>

          <div class="field">
            <label>Cédula</label>
            <input
              name="id_card"
              required
              placeholder="V-00000000"
              value="<?= $client->id_card ?>">
          </div>

          <div class="field">
            <label>Teléfono</label>
            <input
              type="phone"
              name="phone"
              required
              placeholder="0412-0000000"
              value="<?= $client->phone ?>">
          </div>

          <div class="field">
            <label>Dirección</label>
            <textarea
              name="address"
              required
              placeholder="Dirección"><?= $client->address ?></textarea>
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
  </section>
</div>
