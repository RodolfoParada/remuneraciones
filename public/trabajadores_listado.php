<?php
require_once __DIR__ . '/../src/Db.php';
$pageTitle = 'Listado de trabajadores';

try {
  $pdo = (new Db())->pdo();
  $sql = "
    SELECT 
      t.rut_trabajador, t.nombre_completo, c.nombre_cargo,
      tc.nombre_contrato, a.nombre_afp, s.nombre_salud,
      t.sueldo_base_fijo, t.fecha_inicio_contrato, t.fecha_termino_contrato
    FROM trabajador t
    JOIN cargo c ON c.id_cargo = t.id_cargo
    JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
    JOIN afp a ON a.id_afp = t.id_afp
    JOIN sistema_salud s ON s.id_salud = t.id_salud
    ORDER BY t.nombre_completo ASC
  ";
  $rows = $pdo->query($sql)->fetchAll();
} catch (Throwable $e) {
  http_response_code(500);
  $error = 'Error: ' . $e->getMessage();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="panel">
  <div class="tabs">
    <span class="tab active">Trabajadores</span>
    <a class="tab" href="/remuneraciones/public/trabajadores_nuevo.php">+ Nuevo</a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="panel">Error: <?= htmlspecialchars($error) ?></div>
  <?php elseif (!$rows): ?>
    <p class="lead">Aún no hay registros.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>RUT</th><th>Nombre</th><th>Cargo</th><th>Contrato</th>
            <th>AFP</th><th>Salud</th><th> Sueldo base</th>
            <th>Inicio</th><th>Término</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['rut_trabajador']) ?></td>
            <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($r['nombre_cargo']) ?></td>
            <td><?= htmlspecialchars($r['nombre_contrato']) ?></td>
            <td><?= htmlspecialchars($r['nombre_afp']) ?></td>
            <td><?= htmlspecialchars($r['nombre_salud']) ?></td>
            <td>$<?= number_format((float)$r['sueldo_base_fijo'],0,',','.') ?></td>
            <td><?= htmlspecialchars($r['fecha_inicio_contrato']) ?></td>
            <td><?= htmlspecialchars($r['fecha_termino_contrato'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
