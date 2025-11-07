<?php
// public/listado_liquidaciones.php
require_once __DIR__ . '/../src/Db.php';
$pageTitle = 'Listado de liquidaciones';
include __DIR__ . '/includes/header.php';

$errores = [];
$rows = [];

try {
  $pdo = (new Db())->pdo();
  $stmt = $pdo->query("
    SELECT 
      l.id_liquidacion,
      l.rut_trabajador,
      t.nombre_completo,
      l.mes_periodo,
      l.anio_periodo,
      l.liquido_a_pagar
    FROM liquidacion l
    JOIN trabajador t ON t.rut_trabajador = l.rut_trabajador
    ORDER BY l.anio_periodo DESC, l.mes_periodo DESC
  ");
  $rows = $stmt->fetchAll();
} catch (Throwable $e) {
  $errores[] = $e->getMessage();
}
?>

<section class="panel">
  <div class="tabs">
    <span class="tab active">Listado</span>
    <a class="tab" href="/remuneraciones/public/liquidaciones_nueva.php">Nueva liquidación</a>
  </div>

  <?php if ($errores): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f">
      <div class="badge">Se encontraron errores</div>
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <?php if (!$rows): ?>
    <p class="center lead">No existen liquidaciones registradas.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>RUT</th>
          <th>Nombre del trabajador</th>
          <th>Mes</th>
          <th>Año</th>
          <th>Líquido a pagar</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id_liquidacion'] ?></td>
            <td><?= htmlspecialchars($r['rut_trabajador']) ?></td>
            <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
            <td><?= (int)$r['mes_periodo'] ?></td>
            <td><?= (int)$r['anio_periodo'] ?></td>
            <td>$<?= number_format((float)$r['liquido_a_pagar'], 0, ',', '.') ?></td>
            <td>
              <a class="btn small" href="/remuneraciones/public/liquidacion_ver.php?id=<?= (int)$r['id_liquidacion'] ?>">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div class="actions">
    <a class="btn primary" href="/remuneraciones/public/liquidaciones_nueva.php">Registrar nueva liquidación</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
