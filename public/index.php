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

  <a class="card" href="/remuneraciones/public/liquidaciones_nueva.php">
    <h2>Nueva liquidación</h2>
    <br>
    <p class="lead">•  Crear liquidación mensual</p>
  </a>

  <a class="card" href="/remuneraciones/public/listado_liquidaciones.php">
    <h2>Liquidaciones</h2>
    <p class="lead">• Consulta de liquidacion por período</p>
    <p class="lead">• Búsqueda de liquidacion por trabajador</p>
    <p class="lead">• Previsualización de liquidacion por trabajador</p>
    <p class="lead">• Descarga de liquidacion por trabajador</p>
  </a>

</section>

<section class="grid three">
   <a class="card" href="/remuneraciones/public/resumen.php">
    <h2>Guia de Remuneraciones Chile</h2>
    <br>
    <p class="lead">• Conceptos Importantes</p>
  </a>
  <a class="card" href="/remuneraciones/public/parametros.php">
    <h2>Término de Contrato</h2>
    <br>
    <p class="lead">•  Artículo 159</p>
    <p class="lead">•  Articulo 160</p>
    <p class="lead">•  Articulo 161</p>
   
  </a>
  <a class="card" href="/remuneraciones/public/catalogos.php">
    <h2>Catálogos</h2>
    <br>
    <p class="lead">•  Listas de Cargos</p>
    <p class="lead">•  Tipos de Contratos</p>
    <p class="lead">•  Lista de AFP</p>
    <p class="lead">•  Lista de Sistema de Salud</p>
  </a>
 
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

