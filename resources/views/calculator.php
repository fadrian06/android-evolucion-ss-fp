<?php

declare(strict_types=1);

/**
 * @var null|float $rate
 * @var null|string $source
 * @var null|\App\Models\ExchangeRate $customRate
 */

?>

<div class="row g-4">
  <section class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h1 class="card-title h3">Calculadora monetaria</h1>
        <?php if ($rate !== null): ?>
          <p class="text-body-secondary">
            Tasa <?= $source ?>: <strong>Bs. <?= number_format($rate, 2, ',', '.') ?></strong> por USD
          </p>
          <div
            x-data="{ amount: '', direction: 'usd-to-ves', rate: <?= json_encode($rate) ?> }"
            class="row g-3">
            <div class="col-12">
              <label for="amount" class="form-label">Monto</label>
              <input
                id="amount"
                x-model="amount"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                placeholder="Monto">
            </div>
            <div class="col-12">
              <select x-model="direction" class="form-select">
                <option value="usd-to-ves">Dólares a bolívares</option>
                <option value="ves-to-usd">Bolívares a dólares</option>
              </select>
            </div>
            <div class="col-12">
              <output class="fs-3 fw-bold">
                <span x-text="direction === 'usd-to-ves' ? 'Bs. ' : '$'"></span><span
                  x-text="amount === '' ? '0,00' : (direction === 'usd-to-ves' ? amount * rate : amount / rate).toFixed(2).replace('.', ',')"></span>
              </output>
            </div>
          </div>
        <?php else: ?>
          <p class="mb-0 text-danger">No hay una cotización disponible para calcular.</p>
        <?php endif ?>
      </div>
    </div>
  </section>

  <section class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h2 class="card-title h3">Cotización personalizada</h2>
        <p class="text-body-secondary">
          <?= $customRate ? 'Actualiza' : 'Define' ?> la tasa para hoy.
        </p>
        <form method="post" action="./calculadora/cotizacion">
          <label for="custom-rate" class="form-label">Bolívares por dólar</label>
          <div class="input-group">
            <span class="input-group-text">Bs.</span>
            <input
              id="custom-rate"
              name="rate"
              type="number"
              min="0.01"
              step="0.000001"
              value="<?= $customRate?->rate ?? '' ?>"
              required
              class="form-control"
              placeholder="Cotización">
            <button class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>
