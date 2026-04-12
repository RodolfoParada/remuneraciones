<?php $pageTitle = 'Inicio'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/index.css" />

<section class="grid three">
  <a class="card" href="<?= $basePath ?>trabajadores.php">
    <h2>Trabajadores</h2>
    <br>
    <p class="lead">• Lista de trabajadores</p>
    <p class="lead">• Agregar a nuevo trabajador</p>
  </a>

  <a class="card" href="<?= $basePath ?>liquidaciones_nueva.php">
    <h2>Nueva liquidación</h2>
    <br>
    <p class="lead">•  Crear liquidación mensual</p>
  </a>

  <a class="card" href="<?= $basePath ?>listado_liquidaciones.php">
    <h2>Liquidaciones</h2>
    <p class="lead">• Consulta de liquidación por período</p>
    <p class="lead">• Búsqueda de liquidación por trabajador</p>
    <p class="lead">• Previsualización de liquidación por trabajador</p>
    <p class="lead">• Descarga de liquidación por trabajador</p>
  </a>
</section>

<section class="grid three">
  <a class="card" href="<?= $basePath ?>resumen.php">
    <h2>Guia de Remuneraciones Chile</h2>
    <br>
    <p class="lead">• Conceptos Importantes</p>
  </a>
  <a class="card" href="<?= $basePath ?>parametros.php">
    <h2>Término de Contrato</h2>
    <br>
    <p class="lead">•  Artículo 159</p>
    <p class="lead">•  Articulo 160</p>
    <p class="lead">•  Articulo 161</p>
  </a>
  <a class="card" href="<?= $basePath ?>catalogos.php">
    <h2>Catálogos</h2>
    <br>
    <p class="lead">•  Listas de Cargos</p>
    <p class="lead">•  Tipos de Contratos</p>
    <p class="lead">•  Lista de AFP</p>
    <p class="lead">•  Lista de Sistema de Salud</p>
  </a>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>