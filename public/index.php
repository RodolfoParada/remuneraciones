<?php $pageTitle = 'Inicio'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="grid three">
  <a class="card primary" href="/remuneraciones/public/trabajadores.php">
    <h2>Trabajadores</h2>
    <p class="lead">Alta y listado de trabajadores</p>
  </a>

  <a class="card" href="/remuneraciones/public/listado_liquidaciones.php">
    <h2>Liquidaciones</h2>
    <p class="lead">Consulta de liquidaciones por período</p>
  </a>

  <a class="card" href="/remuneraciones/public/resumen.php">
    <h2>Resumen</h2>
    <p class="lead">Indicadores del sistema</p>
  </a>
</section>

<section class="grid three">
  <a class="card" href="/remuneraciones/public/parametros.php">
    <h3>Parámetros</h3>
    <p class="lead">Texto estático / instrucciones</p>
  </a>

  <a class="card" href="/remuneraciones/public/catalogos.php">
    <h3>Catálogos</h3>
    <p class="lead">Listas de referencia</p>
  </a>

  <a class="card" href="/remuneraciones/public/liquidaciones_nueva.php">
    <h3>Nueva liquidación</h3>
    <p class="lead">Crear liquidación mensual</p>
  </a>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

