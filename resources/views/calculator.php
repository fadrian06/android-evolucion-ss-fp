<?php

declare(strict_types=1);

/**
 * @var ?float $rate
 * @var ?float $bcvRate
 * @var string $source
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
    rate: <?= $rate ?>,

    get result() {
      if (!this.amount) return '—';

      return this.direction === 'usd'
        ? 'Bs. ' + (this.amount * this.rate).toFixed(2)
        : '$' + (this.amount / this.rate).toFixed(2);
    },

    get localedRate() {
      return this.rate.toLocaleString('es-VE', {
        maximumFractionDigits: 2,
      });
    }
  }">
  <?php if ($rate): ?>
    <section class="card panel">
      <?php Flight::render('components/eyebrow', [
        'text' => "TASA $source",
      ]) ?>

      <h2>
        Bs.
        <span x-text="localedRate"></span>
        / USD
      </h2>

      <div class="stack">
        <div class="field">
          <label>Monto</label>
          <input
            x-model="amount"
            type="number"
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
        <strong>Bs. <?= number_format($bcvRate, 2, ',', '.') ?> / USD</strong>
      </p>
    <?php endif ?>

    <form method="post" action="./calculadora/cotizacion" class="stack">
      <div class="field">
        <label>Bolívares por dólar</label>
        <input
          name="rate"
          type="number"
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
