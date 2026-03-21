<?php $pageTitle = 'Inicio'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/index.css" />

<section class="grid three">
  <a class="card" href="/remuneraciones/public/trabajadores.php">
    <h2>Trabajadores</h2>
    <br>
    <p class="lead">• Lista de trabajadores</p>
    <p class="lead">• Agregar a nuevo trabajador</p>
  </a>
  <a class="card" href="/remuneraciones/public/listado_liquidaciones.php">
    <h2>Liquidaciones</h2>
    <p class="lead">• Consulta de liquidacion por período</p>
    <p class="lead">• Búsqueda de liquidacion por trabajador</p>
    <p class="lead">• Previsualización de liquidacion por trabajador</p>
    <p class="lead">• Descarga de liquidacion por trabajador</p>
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

