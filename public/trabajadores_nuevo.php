<?php
require_once __DIR__ . '/../src/Db.php';
$pageTitle = 'Nuevo trabajador';

$errores = [];
$ok = false;

try {
  $pdo = (new Db())->pdo();
  $cargos        = $pdo->query("SELECT id_cargo, nombre_cargo FROM cargo ORDER BY nombre_cargo")->fetchAll();
  $tiposContrato = $pdo->query("SELECT id_tipo_contrato, nombre_contrato FROM tipos_contrato ORDER BY nombre_contrato")->fetchAll();
  $afps          = $pdo->query("SELECT id_afp, nombre_afp FROM afp ORDER BY nombre_afp")->fetchAll();
  $sistemasSalud = $pdo->query("SELECT id_salud, nombre_salud FROM sistema_salud ORDER BY nombre_salud")->fetchAll();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut      = trim($_POST['rut_trabajador'] ?? '');
    $nombre   = trim($_POST['nombre_completo'] ?? '');
    $sueldo   = trim($_POST['sueldo_base_fijo'] ?? '');
    $idCargo  = (int)($_POST['id_cargo'] ?? 0);
    $idTipo   = (int)($_POST['id_tipo_contrato'] ?? 0);
    $fInicio  = trim($_POST['fecha_inicio_contrato'] ?? '');
    $fTermino = trim($_POST['fecha_termino_contrato'] ?? '');
    $idAfp    = (int)($_POST['id_afp'] ?? 0);
    $idSalud  = (int)($_POST['id_salud'] ?? 0);

    if ($rut === '') $errores[] = 'El RUT es obligatorio.';
    if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
    if ($sueldo === '' || !ctype_digit($sueldo)) $errores[] = 'El sueldo base debe ser numérico (entero).';
    if ($idCargo <= 0) $errores[] = 'Seleccione un cargo.';
    if ($idTipo <= 0) $errores[] = 'Seleccione un tipo de contrato.';
    if ($fInicio === '') $errores[] = 'La fecha de inicio es obligatoria.';
    if ($idAfp <= 0) $errores[] = 'Seleccione una AFP.';
    if ($idSalud <= 0) $errores[] = 'Seleccione un sistema de salud.';

    if (!$errores) {
      $stmt = $pdo->prepare("
        INSERT INTO trabajador 
          (rut_trabajador, nombre_completo, sueldo_base_fijo, id_cargo, id_tipo_contrato,
           fecha_inicio_contrato, fecha_termino_contrato, id_afp, id_salud)
        VALUES
          (:rut, :nom, :sueldo, :cargo, :tipo, :fini, :fter, :afp, :salud)
      ");
      $stmt->execute([
        ':rut'=>$rut, ':nom'=>$nombre, ':sueldo'=>$sueldo, ':cargo'=>$idCargo, ':tipo'=>$idTipo,
        ':fini'=>$fInicio, ':fter'=>($fTermino !== '' ? $fTermino : null), ':afp'=>$idAfp, ':salud'=>$idSalud,
      ]);
      $ok = true;
    }
  }
} catch (Throwable $e) {
  $errores[] = 'Error de base de datos: ' . $e->getMessage();
}

include __DIR__ . '/includes/header.php';
?>

<section class="panel">
  <div class="tabs">
    <a class="tab" href="/remuneraciones/public/trabajadores_listado.php">Listado</a>
    <span class="tab active">Nuevo</span>
  </div>

  <?php if ($ok): ?>
    <div class="badge success">✅ Trabajador registrado correctamente</div>
    <div class="actions">
      <a class="btn" href="/remuneraciones/public/trabajadores_listado.php">Ir al listado</a>
    </div>
  <?php endif; ?>

  <?php if ($errores): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f">
      <div class="badge">Se encontraron errores</div>
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form">
    <fieldset>
      <legend>Datos del trabajador</legend>

      <label>RUT
        <input type="text" name="rut_trabajador" placeholder="12.345.678-9" required>
      </label>

      <label>Nombre completo
        <input type="text" name="nombre_completo" required>
      </label>

      <label>Sueldo base (entero)
        <input type="number" name="sueldo_base_fijo" min="0" step="1" required>
      </label>

      <label>Cargo
        <select name="id_cargo" required>
          <option value="">-- Seleccione --</option>
          <?php foreach ($cargos as $c): ?>
            <option value="<?= (int)$c['id_cargo'] ?>"><?= htmlspecialchars($c['nombre_cargo']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Tipo de contrato
        <select name="id_tipo_contrato" required>
          <option value="">-- Seleccione --</option>
          <?php foreach ($tiposContrato as $c): ?>
            <option value="<?= (int)$c['id_tipo_contrato'] ?>"><?= htmlspecialchars($c['nombre_contrato']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Fecha inicio contrato
        <input type="date" name="fecha_inicio_contrato" required>
      </label>

      <label>Fecha término contrato
        <input type="date" name="fecha_termino_contrato">
      </label>

      <label>AFP
        <select name="id_afp" required>
          <option value="">-- Seleccione --</option>
          <?php foreach ($afps as $a): ?>
            <option value="<?= (int)$a['id_afp'] ?>"><?= htmlspecialchars($a['nombre_afp']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Sistema de salud
        <select name="id_salud" required>
          <option value="">-- Seleccione --</option>
          <?php foreach ($sistemasSalud as $s): ?>
            <option value="<?= (int)$s['id_salud'] ?>"><?= htmlspecialchars($s['nombre_salud']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </fieldset>

    <div class="actions">
      <button class="btn primary" type="submit">Guardar</button>
      <a class="btn ghost" href="/remuneraciones/public/trabajadores_listado.php">Volver</a>
    </div>
  </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
