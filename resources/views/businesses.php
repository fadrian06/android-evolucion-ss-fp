<?php /** @var \Illuminate\Database\Eloquent\Collection $businesses */ ?>
<header class="page-head"><div><span class="eyebrow">CONFIGURACIÓN</span><h1>Locales</h1><p>Administra los datos de tus puntos de venta.</p></div><button class="btn btn-primary" onclick="document.querySelector('#new').showModal()">+ Nuevo local</button></header>
<dialog class="card" id="new" style="border:0;padding:0;width:min(520px,90vw)"><form class="panel stack" method="post" action="./negocios"><h2>Registrar nuevo local</h2><div class="field"><label>Nombre</label><input name="name" required placeholder="Nombre comercial"></div><div class="field"><label>RIF</label><input name="rif" required placeholder="J-00000000-0"></div><div class="field"><label>Dirección</label><input name="address" required placeholder="Dirección del local"></div><div class="field"><label>Teléfono</label><input name="phone" required placeholder="0412-0000000"></div><div class="actions"><button class="btn btn-primary">Guardar local</button><button type="button" class="btn btn-secondary" onclick="this.closest('dialog').close()">Cancelar</button></div></form></dialog>
<section class="grid grid-3">
  <?php foreach ($businesses as $item): ?>
    <article class="card panel">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px">
        <div style="display:grid;place-items:center;width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#635bff,#9e77ed);color:#fff;font-size:22px;font-weight:800">
          <?= htmlspecialchars(strtoupper(substr($item->name, 0, 1))) ?>
        </div>
        <div>
          <span class="eyebrow">LOCAL #<?= $item->id ?></span>
          <h2 style="margin:2px 0;font-size:18px"><?= htmlspecialchars($item->name) ?></h2>
        </div>
      </div>
      <div class="stack" style="gap:8px;margin-bottom:20px;color:var(--muted)">
        <span><strong style="color:var(--ink)">RIF</strong> · <?= htmlspecialchars($item->rif) ?></span>
        <span><?= htmlspecialchars($item->address) ?></span>
        <span><?= htmlspecialchars($item->phone) ?></span>
      </div>
      <div class="actions">
        <a class="btn btn-primary btn-sm" href="./negocios/<?= $item->id ?>/seleccionar">
          <?= isset($business) && $business->id === $item->id ? 'Local activo' : 'Seleccionar' ?>
        </a>
        <button class="btn btn-secondary btn-sm" onclick="document.querySelector('#edit-<?= $item->id ?>').showModal()">Editar</button>
        <a class="btn btn-danger btn-sm" href="./negocios/<?= $item->id ?>/eliminar">Eliminar</a>
      </div>
    </article>
  <?php endforeach ?>
</section>
<?php foreach ($businesses as $item): ?><dialog id="edit-<?= $item->id ?>" class="card" style="border:0;padding:0;width:min(520px,90vw)"><form class="panel stack" method="post" action="./negocios/<?= $item->id ?>"><h2>Editar <?= htmlspecialchars($item->name) ?></h2><div class="field"><label>Nombre</label><input name="name" value="<?= htmlspecialchars($item->name) ?>" required></div><div class="field"><label>RIF</label><input name="rif" value="<?= htmlspecialchars($item->rif) ?>" required></div><div class="field"><label>Dirección</label><input name="address" value="<?= htmlspecialchars($item->address) ?>" required></div><div class="field"><label>Teléfono</label><input name="phone" value="<?= htmlspecialchars($item->phone) ?>" required></div><div class="actions"><button class="btn btn-primary">Guardar cambios</button><button type="button" class="btn btn-secondary" onclick="this.closest('dialog').close()">Cancelar</button></div></form></dialog><?php endforeach ?>
