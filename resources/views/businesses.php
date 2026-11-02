<?php

declare(strict_types=1);

use App\Models\Business;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Business> $businesses
 * @var ?Business $business
 */

?>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'CONFIGURACIÓN',
  'title' => 'Locales',
  'description' => 'Administra los datos de tus puntos de venta.',
  'buttonText' => '+ Nuevo local',
  'buttonAction' => '$refs.new?.showModal()',
]) ?>

<dialog class="card card-small" x-ref="new">
  <form class="panel stack" method="post" action="./negocios">
    <?php Flight::render('components/toolbar', [
      'title' => 'Registrar nuevo local',
      'inDialog' => true,
    ]) ?>

    <div class="field">
      <label>Nombre</label>
      <input name="name" required placeholder="Nombre comercial">
    </div>

    <div class="field">
      <label>RIF</label>
      <input name="rif" required placeholder="J-00000000-0">
    </div>

    <div class="field">
      <label>Dirección</label>
      <textarea name="address" required placeholder="Dirección del local"></textarea>
    </div>

    <div class="field">
      <label>Teléfono</label>
      <input type="phone" name="phone" required placeholder="0412-0000000">
    </div>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Guardar local',
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
    <label>Buscar negocios</label>
    <input
      x-model="search"
      type="search"
      placeholder="Buscar por nombre, RIF, dirección o teléfono">
  </section>

  <section class="grid grid-3">
    <?php foreach ($businesses as $item): ?>
      <article class="card panel" x-show="matchSearch($el)" x-transition>
        <header class="card-header">
          <div class="card-avatar"><?= $item->name[0] ?></div>
          <div>
            <?php Flight::render('components/eyebrow', [
              'text' => "LOCAL #$item->id",
            ]) ?>

            <h2 class="card-title"><?= $item->name ?></h2>
          </div>
        </header>

        <div class="card-body">
          <div>
            <strong class="card-eyebrow">RIF</strong>
            · <?= $item->rif ?>
          </div>
          <span><?= $item->address ?></span>
          <span><?= $item->phone ?></span>
        </div>

        <footer class="actions">
          <?php Flight::render('components/btn', [
            'text' => $business?->id === $item->id
              ? 'Local activo'
              : 'Seleccionar',
            'href' => "./negocios/$item->id/seleccionar",
            'small' => true,
            'disabled' => $business?->id === $item->id,
            'title' => 'No puedes seleccionar el local que ya está activo',
          ]) ?>

          <?php Flight::render('components/btn', [
            'text' => 'Editar',
            'variant' => 'secondary',
            'small' => true,
            'onclick' => "\$refs.edit$item->id?.showModal()",
          ]) ?>

          <?php Flight::render('components/btn', [
            'text' => 'Eliminar',
            'variant' => 'danger',
            'small' => true,
            'href' => "./negocios/$item->id/eliminar",
            'disabled' => !$item->canBeDeleted(),
            'title' => 'No se puede eliminar un local con registros asociados',
          ]) ?>
        </footer>
      </article>

      <dialog class="card card-small" x-ref="edit<?= $item->id ?>">
        <form
          class="panel stack"
          method="post"
          action="./negocios/<?= $item->id ?>">
          <?php Flight::render('components/toolbar', [
            'title' => "Editar $item->name",
            'inDialog' => true,
          ]) ?>

          <div class="field">
            <label>Nombre</label>
            <input name="name" value="<?= $item->name ?>" required>
          </div>

          <div class="field">
            <label>RIF</label>
            <input name="rif" value="<?= $item->rif ?>" required>
          </div>

          <div class="field">
            <label>Dirección</label>
            <textarea name="address" required><?= $item->address ?></textarea>
          </div>

          <div class="field">
            <label>Teléfono</label>
            <input type="phone" name="phone" value="<?= $item->phone ?>" required>
          </div>

          <div class="actions">
            <?php Flight::render('components/btn', [
              'text' => 'Guardar cambios',
              'type' => 'submit',
            ]) ?>
          </div>
        </form>
      </dialog>
    <?php endforeach ?>
  </section>
</div>
