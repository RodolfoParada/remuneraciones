<?php $pageTitle = 'Trabajadores'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/trabajadores.css" />

<section class="panel">
  <div class="tabs">
    <a class="tab" href="<?= $basePath ?>trabajadores_listado.php">Listado Trabajadores</a>
    <a class="tab" href="<?= $basePath ?>trabajadores_nuevo.php">Crear Nuevo Trabajador</a>
  </div>

  <div class="cards-centradas">
    <a class="card" href="<?= $basePath ?>trabajadores_listado.php">
      <h2>Listado de Trabajadores</h2>
      <br>
      <p class="lead">• Visualiza lista trabajadores</p>
      <p class="lead">• Busqueda de trabajador</p>
      <p class="lead">• Filtra por trabajador activo, no activo, Muestra todos los trabajadores</p>
    </a>
    <a class="card" href="<?= $basePath ?>trabajadores_nuevo.php">
      <h2>Crear Nuevo Trabajador</h2>
       <br>
      <p class="lead">• Formulario permite : <br>Agregar, Modificar, Eliminar, Dar término de contrato a un trabajador</p>
    </a>
  </div>

  <div class="actions">
    <a class="btn ghost btn-volver" href="<?= $basePath ?>index.php">Volver al Inicio</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>