<?php

declare(strict_types=1);

$cards = [
  [
    'eyebrow' => 'VENTAS',
    'title' => 'Registrar venta',
    'description' => 'Registra productos, administra pagos y consulta comprobantes.',
    'href' => './ventas',
    'cta' => 'Ir a ventas →',
  ],
  [
    'eyebrow' => 'INVENTARIO',
    'title' => 'Ver inventario',
    'description' => 'Actualiza precios, categorías y existencias por local.',
    'href' => './productos',
    'cta' => 'Ir a inventario →',
  ],
  [
    'eyebrow' => 'SERVICIO',
    'title' => 'Nueva reparación',
    'description' => 'Registra equipos en servicio y sus comprobantes.',
    'href' => './reparaciones',
    'cta' => 'Ir a reparaciones →',
  ],
];

?>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'PANEL PRINCIPAL',
  'title' => 'Tu operación, bajo control.',
  'description' => 'Accede rápidamente a las tareas diarias de tu local.',
]) ?>

<section class="grid grid-3">
  <?php foreach ($cards as $card): ?>
    <a class="card panel" href="./ventas">
      <?php Flight::render('components/eyebrow', ['text' => $card['eyebrow']]) ?>

      <h2><?= $card['title'] ?></h2>
      <p><?= $card['description'] ?></p>
      <strong class="card-cta"><?= $card['cta'] ?></strong>
    </a>
  <?php endforeach ?>
</section>

<section class="card panel">
  <?php Flight::render('components/eyebrow', ['text' => 'ANTES DE VENDER']) ?>

  <h2>Verifica tu cotización diaria</h2>

  <p>
    La cotización personalizada define el valor en bolívares que quedará
    guardado en cada comprobante.
  </p>

  <?php Flight::render('components/btn', [
    'text' => 'Abrir calculadora',
    'href' => './calculadora',
  ]) ?>
</section>
