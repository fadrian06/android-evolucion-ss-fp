<?php

declare(strict_types=1);

use App\Models\Business;
use Illuminate\Database\Eloquent\Collection;

/**
 * @var Collection<int, Business> $businesses
 * @var ?Business $business
 */

?>

<style>
  .disabled-action {
    position: relative;
    display: inline-flex
  }

  .disabled-action:hover:after {
    content: 'No se puede eliminar un local con registros asociados';
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
  'eyebrow' => 'CONFIGURACIÓN',
  'title' => 'Locales',
  'description' => 'Administra los datos de tus puntos de venta.',
  'buttonText' => '+ Nuevo local',
  'buttonAction' => '$refs.new?.showModal()',
]) ?>

<dialog class="card card-small" x-ref="new">
  <form class="panel stack" method="post" action="./negocios">
    <h2>Registrar nuevo local</h2>

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
      <input name="address" required placeholder="Dirección del local">
    </div>

    <div class="field">
      <label>Teléfono</label>
      <input name="phone" required placeholder="0412-0000000">
    </div>

    <div class="actions">
      <?php Flight::render('components/btn', [
        'text' => 'Guardar local',
        'type' => 'submit',
      ]) ?>

      <?php Flight::render('components/btn', [
        'text' => 'Cancelar',
        'variant' => 'secondary',
        'onclick' => '$el.closest(`dialog`).close()',
      ]) ?>
    </div>
  </form>
</dialog>

<section class="grid grid-3">
  <?php foreach ($businesses as $item): ?>
    <article class="card panel">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px">
        <div style="display:grid;place-items:center;width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#635bff,#9e77ed);color:#fff;font-size:22px;font-weight:800">
          <?= strtoupper($item->name[0]) ?>
        </div>
        <div>
          <?php Flight::render('components/eyebrow', [
            'text' => "LOCAL #$item->id",
          ]) ?>

          <h2 style="margin:2px 0;font-size:18px"><?= $item->name ?></h2>
        </div>
      </div>

      <div
        class="stack"
        style="gap:8px;margin-bottom:20px;color:var(--muted)">
        <span>
          <strong style="color:var(--ink)">RIF</strong>
          · <?= $item->rif ?>
        </span>
        <span><?= $item->address ?></span>
        <span><?= $item->phone ?></span>
      </div>
      <div class="actions">
        <?php Flight::render('components/btn', [
          'text' => $business?->id === $item->id
            ? 'Local activo'
            : 'Seleccionar',
          'href' => "./negocios/$item->id/seleccionar",
          'small' => true,
        ]) ?>

        <?php Flight::render('components/btn', [
          'text' => 'Editar',
          'variant' => 'secondary',
          'small' => true,
          'onclick' => "\$refs.edit$item->id?.showModal()",
        ]) ?>

        <?php if ($item->hasAssociatedRecords()): ?>
          <span class="disabled-action">
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
            'href' => "./negocios/$item->id/eliminar",
          ]) ?>
        <?php endif ?>
      </div>
    </article>
  <?php endforeach ?>
</section>

<?php foreach ($businesses as $item): ?>
  <dialog class="card card-small" x-ref="edit<?= $item->id ?>">
    <form
      class="panel stack"
      method="post"
      action="./negocios/<?= $item->id ?>">
      <h2>Editar <?= $item->name ?></h2>

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
        <input name="address" value="<?= $item->address ?>" required>
      </div>

      <div class="field">
        <label>Teléfono</label>
        <input name="phone" value="<?= $item->phone ?>" required>
      </div>

      <div class="actions">
        <?php Flight::render('components/btn', [
          'text' => 'Guardar cambios',
          'type' => 'submit',
        ]) ?>

        <?php Flight::render('components/btn', [
          'text' => 'Cancelar',
          'variant' => 'secondary',
          'onclick' => '$el.closest(`dialog`).close()',
        ]) ?>
      </div>
    </form>
  </dialog>
<?php endforeach ?>
