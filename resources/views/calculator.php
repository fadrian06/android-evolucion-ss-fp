<?php

declare(strict_types=1);

use App\Models\ExchangeRate;

/**
 * @var ?float $rate
 * @var ?float $bcvRate
 * @var string $source
 * @var ?ExchangeRate $latestExchangeRate
 */

?>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'HERRAMIENTA',
  'title' => 'Calculadora',
  'description' => 'Convierte con la cotización vigente.',
]) ?>

<div
  class="grid grid-2"
  x-data="{
    amount: '',
    direction: 'usd',
    rate: <?= $latestExchangeRate?->rate ?: $rate ?: 0 ?>,
    bcvRate: <?= $bcvRate ?: 0 ?>,
    formatter: Intl.NumberFormat('es-VE'),

    get result() {
      if (!this.amount) return '—';

      return this.direction === 'usd'
        ? 'Bs. ' + this.formatter.format(this.amount * this.rate)
        : '$ ' + this.formatter.format(this.amount / this.rate);
    },

    getLocaledRate(rate = 0) {
      return 'Bs. ' + this.formatter.format(rate) + ' / $';
    }
  }">
  <?php if ($rate): ?>
    <section class="card panel">
      <?php Flight::render('components/eyebrow', [
        'text' => "TASA $source",
      ]) ?>

      <h2 x-text="getLocaledRate(rate)"></h2>

      <div class="stack">
        <div class="field">
          <label>Monto</label>
          <input
            x-model="amount"
            type="number"
            :data-currency="direction"
            min="0"
            step="0.01"
            placeholder="0,00">
        </div>

        <div class="field">
          <label>Conversión</label>
          <select x-model="direction">
            <option value="usd">Dólares a bolívares</option>
            <option value="ves">Bolívares a dólares</option>
          </select>
        </div>

        <div class="metric card">
          <span class="metric-label">Resultado</span>
          <strong class="metric-value" x-text="result"></strong>
        </div>
      </div>
    </section>
  <?php endif ?>

  <section class="card panel">
    <?php Flight::render('components/eyebrow', ['text' => 'ADMINISTRACIÓN']) ?>

    <h2>Cotización personalizada</h2>
    <p>Esta tasa es necesaria para generar comprobantes hoy.</p>

    <?php if ($bcvRate): ?>
      <p class="muted">
        Referencia BCV actualizada:
        <strong x-text="getLocaledRate(bcvRate)"></strong>
      </p>
    <?php endif ?>

    <form method="post" action="./calculadora/cotizacion" class="stack">
      <div class="field">
        <label>Bolívares por dólar</label>
        <input
          name="rate"
          type="number"
          data-currency="usd"
          min=".01"
          step=".000001"
          x-model="rate"
          required
          placeholder="Cotización">
      </div>
      <?php Flight::render('components/btn', [
        'text' => 'Guardar cotización',
        'type' => 'submit',
      ]) ?>
    </form>
  </section>
</div>
