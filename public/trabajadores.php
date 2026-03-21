<?php $pageTitle = 'Trabajadores'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/trabajadores.css" />

<section class="panel">
  <div class="tabs">
    <a class="tab" href="/remuneraciones/public/trabajadores_listado.php">Listado Trabajadores</a>
    <a class="tab" href="/remuneraciones/public/trabajadores_nuevo.php">Nuevo Trabajador</a>
  </div>

  <div class="cards-centradas">
    <a class="card" href="/remuneraciones/public/trabajadores_listado.php">
      <h3>Ver listado de Trabajadores</h3>
      <br>
      <p class="lead">• Visualiza lista trabajadores</p>
      <p class="lead">• Busqueda de trabajador</p>
      <p class="lead">• Filtra por trabajador activo, no activo, Muestra todos los trabajadores</p>
    </a>
    <a class="card" href="/remuneraciones/public/trabajadores_nuevo.php">
      <h3>Agregar Nuevo Trabajador</h3>
       <br>
      <p class="lead">• Formulario permite : <br>Agregar, Modificar, Eliminar, Dar término de contrato a un trabajador</p>
    </a>
  </div>

  <div class="actions">
    <a class="btn ghost  btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
