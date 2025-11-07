<?php $pageTitle = 'Resumen'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="panel">
  <div class="tabs"><span class="tab active">Resumen</span></div>

  <div class="grid three">
    <div class="stat highlight">
      <div class="stat-label">Trabajadores activos</div>
      <div class="stat-value">—</div>
    </div>
    <div class="stat">
      <div class="stat-label">Liquidaciones del mes</div>
      <div class="stat-value">—</div>
    </div>
    <div class="stat">
      <div class="stat-label">Última actualización</div>
      <div class="stat-value"><?= date('Y-m-d H:i') ?></div>
    </div>
  </div>

  <p class="lead">Esta vista puede ampliarse con consultas SQL para indicadores.</p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
