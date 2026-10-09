<?php /** @var \Illuminate\Database\Eloquent\Collection $clients */ ?>
<style>.disabled-client-action{position:relative;display:inline-flex}.disabled-client-action:hover:after{content:'No se puede eliminar un cliente con registros asociados';position:absolute;z-index:10;bottom:calc(100% + 8px);left:50%;width:max-content;max-width:220px;padding:8px 10px;border-radius:8px;background:#182230;color:#fff;font-size:11px;font-weight:600;line-height:1.35;text-align:center;box-shadow:var(--shadow);transform:translateX(-50%)}</style>
<header class="page-head"><div><span class="eyebrow">DIRECTORIO</span><h1>Clientes</h1><p>Información de contacto y ventas.</p></div><button class="btn btn-primary" onclick="document.querySelector('#new').showModal()">+ Nuevo cliente</button></header>
<div class="field" style="margin:0 0 20px"><label for="client-search">Buscar clientes</label><input id="client-search" type="search" placeholder="Nombre, cédula, teléfono o dirección"></div>
<dialog class="card" id="new" style="border:0;padding:0;width:min(520px,90vw)"><form class="panel stack" method="post" action="./clientes"><h2>Registrar cliente</h2><div class="field"><label>Nombre</label><input name="name" required placeholder="Nombre completo"></div><div class="field"><label>Cédula</label><input name="id_card" required placeholder="V-00000000"></div><div class="field"><label>Teléfono</label><input name="phone" required placeholder="0412-0000000"></div><div class="field"><label>Dirección</label><input name="address" required placeholder="Dirección"></div><div class="actions"><button class="btn btn-primary">Guardar cliente</button><button type="button" class="btn btn-secondary" onclick="this.closest('dialog').close()">Cancelar</button></div></form></dialog>
<section class="grid grid-3">
  <?php foreach ($clients as $client): ?>
    <article class="card panel client-card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px">
        <div style="display:grid;place-items:center;width:52px;height:52px;border-radius:50%;background:#e4e2ff;color:#5149d6;font-size:17px;font-weight:800">
          <?= htmlspecialchars(strtoupper(substr($client->name, 0, 2))) ?>
        </div>
        <div>
          <span class="eyebrow">CLIENTE #<?= $client->id ?></span>
          <h2 style="margin:2px 0;font-size:18px"><?= htmlspecialchars($client->name) ?></h2>
        </div>
      </div>
      <div class="stack" style="gap:8px;margin-bottom:20px;color:var(--muted)">
        <span><strong style="color:var(--ink)">C.I.</strong> · <?= htmlspecialchars($client->id_card) ?></span>
        <span><?= htmlspecialchars($client->phone) ?></span>
        <span><?= htmlspecialchars($client->address) ?></span>
      </div>
      <?php $hasAssociatedRecords = $client->sales_count + $client->repairs_count + $client->layaways_count > 0; ?>
      <div class="actions">
        <button class="btn btn-secondary btn-sm" onclick="document.querySelector('#edit-<?= $client->id ?>').showModal()">Editar</button>
        <?php if ($hasAssociatedRecords): ?><span class="disabled-client-action"><button class="btn btn-danger btn-sm" type="button" disabled style="cursor:not-allowed;opacity:.45;filter:grayscale(1)">Eliminar</button></span><?php else: ?><a class="btn btn-danger btn-sm" href="./clientes/<?= $client->id ?>/eliminar">Eliminar</a><?php endif ?>
      </div>
    </article>
  <?php endforeach ?>
</section>
<script>document.addEventListener('DOMContentLoaded',()=>document.querySelector('#client-search').addEventListener('input',event=>document.querySelectorAll('.client-card').forEach(card=>card.hidden=!card.textContent.toLowerCase().includes(event.target.value.toLowerCase()))))</script>
<?php foreach ($clients as $client): ?><dialog id="edit-<?= $client->id ?>" class="card" style="border:0;padding:0;width:min(520px,90vw)"><form class="panel stack" method="post" action="./clientes/<?= $client->id ?>"><h2>Editar cliente</h2><?php foreach (['name'=>'Nombre','id_card'=>'Cédula','phone'=>'Teléfono','address'=>'Dirección'] as $key=>$label): ?><div class="field"><label><?= $label ?></label><input name="<?= $key ?>" value="<?= htmlspecialchars($client->$key) ?>" required></div><?php endforeach ?><div class="actions"><button class="btn btn-primary">Guardar</button><button type="button" class="btn btn-secondary" onclick="this.closest('dialog').close()">Cancelar</button></div></form></dialog><?php endforeach ?>
