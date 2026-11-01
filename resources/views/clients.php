<?php

declare(strict_types=1);

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

/** @var Collection<int, Client> $clients */

$labels = [
  'name' => 'Nombre',
  'id_card' => 'Cédula',
  'phone' => 'Teléfono',
  'address' => 'Dirección',
];

?>

<style>
  .disabled-client-action {
    position: relative;
    display: inline-flex
  }

  .disabled-client-action:hover:after {
    content: 'No se puede eliminar un cliente con registros asociados';
    position: absolute;
    z-index: 10;
    bottom: calc(100% + 8px);
    left: 50%;
    width: max-content;
    max-width: 220px;
    padding: 8px 10px;
    border-radius: 8px;
    background: #182230;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.35;
    text-align: center;
    box-shadow: var(--shadow);
    transform: translateX(-50%)
  }
</style>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'DIRECTORIO',
  'title' => 'Clientes',
  'description' => 'Información de contacto y ventas.',
  'buttonText' => '+ Nuevo cliente',
  'buttonAction' => 'document.querySelector(`#new`).showModal()',
]) ?>

<div class="field" style="margin:0 0 20px">
  <label for="client-search">Buscar clientes</label>
  <input
    id="client-search"
    type="search"
    placeholder="Nombre, cédula, teléfono o dirección">
</div>

<dialog class="card card-small" id="new">
  <form class="panel stack" method="post" action="./clientes">
    <h2>Registrar cliente</h2>

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
      <input name="phone" required placeholder="0412-0000000">
    </div>

    <div class="field">
      <label>Dirección</label>
      <input name="address" required placeholder="Dirección">
    </div>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Guardar cliente',
        'type' => 'submit',
      ]) ?>

      <?php Flight::render('components/btn', [
        'text' => 'Cancelar',
        'variant' => 'secondary',
        'onclick' => 'this.closest(`dialog`).close()',
      ]) ?>
    </div>
  </form>
</dialog>

<section class="grid grid-3">
  <?php foreach ($clients as $client): ?>
    <article class="card panel client-card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px">
        <div style="display:grid;place-items:center;width:52px;height:52px;border-radius:50%;background:#e4e2ff;color:#5149d6;font-size:17px;font-weight:800">
          <?= strtoupper(substr($client->name, 0, 2)) ?>
        </div>
        <div>
          <?php Flight::render('components/eyebrow', [
            'text' => "CLIENTE #$client->id",
          ]) ?>

          <h2 style="margin:2px 0;font-size:18px"><?= $client->name ?></h2>
        </div>
      </div>

      <div
        class="stack"
        style="gap:8px;margin-bottom:20px;color:var(--muted)">
        <span>
          <strong style="color:var(--ink)">C.I.</strong>
          · <?= $client->id_card ?>
        </span>
        <span><?= $client->phone ?></span>
        <span><?= $client->address ?></span>
      </div>

      <div class="actions">
        <?php Flight::render('components/btn', [
          'text' => 'Editar',
          'variant' => 'secondary',
          'small' => true,
          'onclick' => "document.querySelector(`#edit-$client->id`).showModal()",
        ]) ?>

        <?php if ($client->hasAssociatedRecords()): ?>
          <span class="disabled-client-action">
            <?php Flight::render('components/btn', [
              'text' => 'Eliminar',
              'variant' => 'danger',
              'small' => true,
              'disabled' => true,
              'style' => 'cursor:not-allowed;opacity:.45;filter:grayscale(1)',
            ]) ?>
          </span>
        <?php else: ?>
          <?php Flight::render('components/btn', [
            'text' => 'Eliminar',
            'variant' => 'danger',
            'small' => true,
            'href' => "./clientes/$client->id/eliminar",
          ]) ?>
        <?php endif ?>
      </div>
    </article>
  <?php endforeach ?>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => document.querySelector('#client-search').addEventListener('input', event => document.querySelectorAll('.client-card').forEach(card => card.hidden = !card.textContent.toLowerCase().includes(event.target.value.toLowerCase()))))
</script>

<?php foreach ($clients as $client): ?>
  <dialog id="edit-<?= $client->id ?>" class="card card-small">
    <form
      class="panel stack"
      method="post"
      action="./clientes/<?= $client->id ?>">
      <h2>Editar cliente</h2>

      <?php foreach ($labels as $key => $label): ?>
        <div class="field">
          <label><?= $label ?></label>
          <input name="<?= $key ?>" value="<?= $client->$key ?>" required>
        </div>
      <?php endforeach ?>

      <div class="actions">
        <?php Flight::render('components/btn', [
          'text' => 'Guardar',
          'type' => 'submit',
        ]) ?>

        <?php Flight::render('components/btn', [
          'text' => 'Cancelar',
          'variant' => 'secondary',
          'onclick' => 'this.closest(`dialog`).close()',
        ]) ?>
      </div>
    </form>
  </dialog>
<?php endforeach ?>
