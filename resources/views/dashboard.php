<?php

declare(strict_types=1);

?>

<?php Flight::render('components/page-head', [
  'eyebrow' => 'PANEL PRINCIPAL',
  'title' => 'Tu operación, bajo control.',
  'description' => 'Accede rápidamente a las tareas diarias de tu local.',
]) ?>

<section class="grid grid-3">
  <a class="card panel" href="./ventas">
    <?php Flight::render('components/eyebrow', ['text' => 'VENTAS']) ?>

    <h2>Registrar venta</h2>
    <p>Registra productos, administra pagos y consulta comprobantes.</p>
    <strong style="color:var(--brand)">Ir a ventas →</strong>
  </a>

  <a class="card panel" href="./productos">
    <?php Flight::render('components/eyebrow', ['text' => 'INVENTARIO']) ?>

    <h2>Ver inventario</h2>
    <p>Actualiza precios, categorías y existencias por local.</p>
    <strong style="color:var(--brand)">Ir a inventario →</strong>
  </a>

  <a class="card panel" href="./reparaciones">
    <?php Flight::render('components/eyebrow', ['text' => 'SERVICIO']) ?>

    <h2>Nueva reparación</h2>
    <p>Registra equipos en servicio y sus comprobantes.</p>
    <strong style="color:var(--brand)">Ir a reparaciones →</strong>
  </a>
</section>

<section class="card panel" style="margin-top:20px">
  <?php Flight::render('components/eyebrow', ['text' => 'ANTES DE VENDER']) ?>

  <h2>Verifica tu cotización diaria</h2>
  <p>
    La cotización personalizada define el valor en bolívares que quedará
    guardado en cada comprobante.
  </p>
  <a class="btn btn-primary" href="./calculadora">Abrir calculadora</a>
</section>
