<?php /** @var \Illuminate\Database\Eloquent\Collection $sales */ /** @var \Illuminate\Database\Eloquent\Collection $clients */ /** @var \Illuminate\Database\Eloquent\Collection $products */ /** @var \Illuminate\Database\Eloquent\Collection $businesses */ ?>
<?php if($invoiceId): foreach(explode(',',$invoiceId) as $id): ?><script>open('./ventas/<?= $id ?>','_blank')</script><?php endforeach; endif ?>
<style>
  .select-search { position: relative; align-self: start; }
  .select-search > select { display: none; }
  .select-search__trigger { width: 100%; min-height: 42px; padding: 10px 36px 10px 11px; border: 1px solid #d0d5dd; border-radius: 8px; background: #fff; color: #182230; text-align: left; position: relative; }
  .select-search__trigger::after { content: '⌄'; position: absolute; right: 12px; color: #667085; }
  .select-search__trigger:focus { border-color: var(--brand); box-shadow: 0 0 0 3px #635bff1c; outline: 0; }
  .select-search__menu { display: none; position: absolute; z-index: 20; top: calc(100% + 5px); width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: var(--shadow); }
  .select-search.is-open .select-search__menu { display: grid; gap: 6px; }
  .select-search__input { width: 100%; padding: 9px 10px; border: 1px solid #d0d5dd; border-radius: 7px; outline: 0; }
  .select-search__options { max-height: 210px; overflow-y: auto; display: grid; gap: 2px; }
  .select-search__option { width: 100%; padding: 9px 10px; border: 0; border-radius: 7px; background: transparent; color: var(--ink); text-align: left; }
  .select-search__option:hover, .select-search__option.is-selected { background: #eef2ff; color: var(--brand-dark); }
  .select-search__empty { padding: 9px 10px; color: var(--muted); font-size: 12px; }
  .sale-modal { width: min(1180px, 92vw); max-height: 90vh; padding: 0; border: 0; overflow: auto; }
  .sale-modal::backdrop { background: rgb(17 24 39 / 55%); }
  .sale-modal .sale-builder { grid-template-columns: minmax(0, 1fr); }
  .sale-modal .item-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
</style>
<header class="page-head"><div><span class="eyebrow">FACTURACIÓN</span><h1>Ventas</h1><p>Facturas, cobros y estado de cada operación.</p></div><button class="btn btn-primary" onclick="document.querySelector('#new').showModal()">+ Nueva venta</button></header>
<dialog class="card sale-modal" id="new"><form method="post" action="./ventas" class="panel sale-builder" id="sale-form"><section class="stack"><div class="toolbar" style="margin:-22px -22px 6px"><h2>Crear factura</h2><button type="button" class="btn btn-secondary btn-sm" onclick="this.closest('dialog').close()">Cancelar</button></div><div class="field"><label>Cliente</label><select name="client_id" required class="searchable-select"><option value="">Seleccionar cliente</option><?php foreach($clients as $client): ?><option value="<?= $client->id ?>"><?= htmlspecialchars($client->name) ?></option><?php endforeach ?></select></div><div id="items"><div class="item-row"><div class="field"><label>Producto</label><select name="product_id[]" required class="product searchable-select"><option value="">Seleccionar producto</option><?php foreach($products as $product): $availableBusinesses=$product->batches->filter(fn($batch)=>$batch->stock>0)->pluck('business_id')->implode(','); ?><option value="<?= $product->id ?>" data-category="<?= $product->category ?>" data-businesses="<?= htmlspecialchars($availableBusinesses, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($product->name) ?> · $<?= $product->price ?></option><?php endforeach ?></select></div><div class="field"><label>Local de origen</label><select name="business_id[]" required class="business searchable-select"><option value="">Seleccionar</option><?php foreach($businesses as $local): ?><option value="<?= $local->id ?>"><?= htmlspecialchars($local->name) ?></option><?php endforeach ?></select></div><div class="field"><label>Cantidad</label><input name="quantity[]" type="number" min="1" value="1" required class="quantity"></div><div class="field identifier"><label>IMEI / código</label><input name="imei1[]" placeholder="IMEI 1"><input name="imei2[]" placeholder="IMEI 2" style="margin-top:6px"><div class="code-field hidden"><input name="code[]" placeholder="Código" style="margin-top:6px"></div></div></div></div><button type="button" class="btn btn-secondary" id="add-item">+ Añadir producto</button></section><aside class="card panel stack" id="payments"><h2 style="margin:0">Pago inicial</h2><p style="margin:0;color:var(--muted)">No disponible al facturar teléfonos.</p><div id="payment-list"><div class="field"><label>Monto USD</label><input name="amount[]" type="number" min="1" placeholder="0" required><select name="method[]" required class="searchable-select" style="margin-top:6px"><option value="Físico">Físico</option><option>Punto</option><option>Transferencia</option></select></div></div><button type="button" class="btn btn-secondary" id="add-payment">+ Añadir pago</button></aside><div class="actions"><button class="btn btn-primary">Facturar</button></div></form></dialog>
<section class="card table-wrap"><div class="toolbar"><h2><?= $sales->count() ?> facturas</h2></div><table class="data-table"><thead><tr><th>Factura</th><th>Cliente</th><th>Productos</th><th>Total</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach($sales as $sale): ?><tr><td><strong>#<?= $sale->id ?></strong><br><small><?= $sale->created_at->format('d/m/Y') ?></small></td><td><?= htmlspecialchars($sale->client->name) ?></td><td><?php foreach($sale->items as $item): ?><small><?= htmlspecialchars($item->product->name) ?> × <?= $item->quantity ?><br></small><?php endforeach ?></td><td>$<?= $sale->getTotal() ?><br><small>Bs. <?= number_format($sale->getTotalVes(),2,',','.') ?></small></td><td><span class="status <?= $sale->cancelled_at?'status-danger':($sale->getRemainingAmount()>0?'status-warning':'status-success') ?>"><?= $sale->cancelled_at?'Anulada':($sale->getRemainingAmount()>0?'Pendiente':'Pagada') ?></span></td><td class="actions"><a class="btn btn-secondary btn-sm" target="_blank" href="./ventas/<?= $sale->id ?>">Factura</a><?php if(!$sale->cancelled_at): ?><form method="post" action="./ventas/<?= $sale->id ?>/anular"><button class="btn btn-danger btn-sm" onclick="return confirm('¿Anular y restaurar stock?')">Anular</button></form><?php endif ?></td></tr><?php endforeach ?></tbody></table></section>
<script>
  const items = document.querySelector('#items');
  const template = items.firstElementChild.outerHTML;

  function enhanceSelect(select) {
    if (select.dataset.enhanced) return;
    select.dataset.enhanced = 'true';

    const wrapper = document.createElement('div');
    wrapper.className = 'select-search';
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'select-search__trigger';
    const menu = document.createElement('div');
    menu.className = 'select-search__menu';
    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'select-search__input';
    search.placeholder = 'Buscar...';
    const options = document.createElement('div');
    options.className = 'select-search__options';

    const updateTrigger = () => {
      trigger.textContent = select.selectedOptions[0]?.textContent.trim() || 'Seleccionar';
    };
    const renderOptions = () => {
      const query = search.value.toLowerCase();
      options.innerHTML = '';
      [...select.options].filter(option => option.value && !option.hidden && option.textContent.toLowerCase().includes(query)).forEach(option => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `select-search__option${option.selected ? ' is-selected' : ''}`;
        button.textContent = option.textContent.trim();
        button.onclick = () => {
          select.value = option.value;
          select.dispatchEvent(new Event('change', { bubbles: true }));
          updateTrigger();
          wrapper.classList.remove('is-open');
        };
        options.append(button);
      });
      if (!options.children.length) options.innerHTML = '<div class="select-search__empty">Sin resultados</div>';
    };

    select.before(wrapper);
    wrapper.append(select, trigger, menu);
    menu.append(search, options);
    select.refreshSearchOptions = () => {
      updateTrigger();
      renderOptions();
    };
    updateTrigger();
    renderOptions();
    trigger.onclick = () => {
      document.querySelectorAll('.select-search.is-open').forEach(element => element !== wrapper && element.classList.remove('is-open'));
      wrapper.classList.toggle('is-open');
      if (wrapper.classList.contains('is-open')) {
        search.value = '';
        renderOptions();
        search.focus();
      }
    };
    search.oninput = renderOptions;
  }

  function filterBusinesses(product) {
    const business = product.closest('.item-row').querySelector('.business');
    const availableBusinesses = (product.selectedOptions[0]?.dataset.businesses || '').split(',');
    [...business.options].forEach(option => {
      option.hidden = Boolean(option.value) && !availableBusinesses.includes(option.value);
    });
    if (business.selectedOptions[0]?.hidden) business.value = '';
    business.refreshSearchOptions();
  }

  document.querySelectorAll('.searchable-select').forEach(enhanceSelect);
  document.querySelectorAll('.product').forEach(filterBusinesses);
  document.addEventListener('click', event => {
    if (!event.target.closest('.select-search')) document.querySelectorAll('.select-search.is-open').forEach(element => element.classList.remove('is-open'));
  });
  document.querySelector('#add-item').onclick = () => {
    items.insertAdjacentHTML('beforeend', template);
    items.lastElementChild.querySelectorAll('.searchable-select').forEach(enhanceSelect);
    items.lastElementChild.querySelectorAll('.product').forEach(filterBusinesses);
  };
  document.querySelector('#sale-form').onchange = () => {
    const phone = [...document.querySelectorAll('.product')].some(select => select.selectedOptions[0]?.dataset.category === 'phone');
    document.querySelectorAll('.product').forEach(select => {
      const code = select.closest('.item-row').querySelector('.code-field');
      const isAccessory = select.selectedOptions[0]?.dataset.category === 'accessory';
      code.classList.toggle('hidden', !isAccessory);
      code.querySelector('input').required = isAccessory;
      if (!isAccessory) code.querySelector('input').value = '';
    });
    document.querySelectorAll('.product').forEach(filterBusinesses);
    const payments = document.querySelector('#payments');
    payments.classList.toggle('hidden', phone);
    payments.querySelectorAll('input,select').forEach(input => input.disabled = phone);
  };
</script>
