<?php
require_once __DIR__ . '/../src/Db.php';
$pageTitle = 'Catálogos';
include __DIR__ . '/includes/header.php';

$errores = [];
$cat = ['cargo'=>[], 'tipos_contrato'=>[], 'afp'=>[], 'sistema_salud'=>[]];

try {
  $pdo = (new Db())->pdo();
  $cat['cargo']          = $pdo->query("SELECT id_cargo, nombre_cargo FROM cargo ORDER BY nombre_cargo")->fetchAll();
  $cat['tipos_contrato'] = $pdo->query("SELECT id_tipo_contrato, nombre_contrato FROM tipos_contrato ORDER BY nombre_contrato")->fetchAll();
  $cat['afp']            = $pdo->query("SELECT id_afp, nombre_afp, porcentaje_descuento FROM afp ORDER BY nombre_afp")->fetchAll();
  $cat['sistema_salud']  = $pdo->query("SELECT id_salud, nombre_salud FROM sistema_salud ORDER BY nombre_salud")->fetchAll();
} catch (Throwable $e) {
  $errores[] = 'Error de base de datos: ' . $e->getMessage();
}
?>

<section class="panel">
  <div class="tabs"><span class="tab active">Catálogos</span></div>

  <?php if ($errores): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f">
      <div class="badge">Se encontraron errores</div>
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php else: ?>
    <div class="grid two">

      <div class="card">
        <h3>Cargos</h3>
        <?php if (!$cat['cargo']): ?>
          <p class="lead">Sin registros.</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>ID</th><th>Nombre</th></tr></thead>
            <tbody>
            <?php foreach ($cat['cargo'] as $r): ?>
              <tr><td><?= (int)$r['id_cargo'] ?></td><td><?= htmlspecialchars($r['nombre_cargo']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>Tipos de contrato</h3>
        <?php if (!$cat['tipos_contrato']): ?>
          <p class="lead">Sin registros.</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>ID</th><th>Nombre</th></tr></thead>
            <tbody>
            <?php foreach ($cat['tipos_contrato'] as $r): ?>
              <tr><td><?= (int)$r['id_tipo_contrato'] ?></td><td><?= htmlspecialchars($r['nombre_contrato']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>AFP</h3>
        <?php if (!$cat['afp']): ?>
          <p class="lead">Sin registros.</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>ID</th><th>Nombre</th><th>% desc.</th></tr></thead>
            <tbody>
            <?php foreach ($cat['afp'] as $r): ?>
              <tr>
                <td><?= (int)$r['id_afp'] ?></td>
                <td><?= htmlspecialchars($r['nombre_afp']) ?></td>
                <td><?= number_format((float)$r['porcentaje_descuento'],2,',','.') ?>%</td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>Sistema de salud</h3>
        <?php if (!$cat['sistema_salud']): ?>
          <p class="lead">Sin registros.</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>ID</th><th>Nombre</th></tr></thead>
            <tbody>
            <?php foreach ($cat['sistema_salud'] as $r): ?>
              <tr><td><?= (int)$r['id_salud'] ?></td><td><?= htmlspecialchars($r['nombre_salud']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
