<?php $pageTitle = 'Trabajadores'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="panel">
  <div class="tabs">
    <span class="tab active">Trabajadores</span>
    <a class="tab" href="/trabajadores_listado.php">Listado</a>
    <a class="tab" href="/trabajadores_nuevo.php">+ Nuevo</a>
  </div>

  <div class="grid three">
    <a class="card" href="/remuneraciones/public/trabajadores_listado.php">
      <h3>Ver listado</h3>
      <p class="lead">Consulta y búsqueda</p>
    </a>
    <a class="card" href="/remuneraciones/public/trabajadores_nuevo.php">
      <h3>Registrar trabajador</h3>
      <p class="lead">Formulario de alta</p>
    </a>
    <a class="card" href="/remuneraciones/public/index.php">
      <h3>Volver</h3>
      <p class="lead">Menú principal</p>
    </a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

