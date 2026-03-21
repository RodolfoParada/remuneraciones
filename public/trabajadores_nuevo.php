<?php
require_once __DIR__ . '/../src/Db.php';

$pageTitle = 'Trabajadores';
$errores   = [];
$ok        = false;
$okMsg     = '';

try {
    $pdo = (new Db())->pdo();

    $cargos        = $pdo->query("SELECT id_cargo, nombre_cargo FROM cargo ORDER BY nombre_cargo")->fetchAll();
    $tiposContrato = $pdo->query("SELECT id_tipo_contrato, nombre_contrato FROM tipos_contrato ORDER BY nombre_contrato")->fetchAll();
    $afps          = $pdo->query("SELECT id_afp, nombre_afp FROM afp ORDER BY nombre_afp")->fetchAll();
    $sistemasSalud = $pdo->query("SELECT id_salud, nombre_salud FROM sistema_salud ORDER BY nombre_salud")->fetchAll();

    $todosTrabajadores = $pdo->query("
        SELECT t.rut_trabajador, t.nombre_completo, t.sueldo_base_fijo,
               t.id_cargo, t.id_tipo_contrato, t.id_afp, t.id_salud,
               t.fecha_inicio_contrato, t.fecha_termino_contrato,
               c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
        FROM trabajador t
        JOIN cargo c ON c.id_cargo = t.id_cargo
        JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
        JOIN afp a ON a.id_afp = t.id_afp
        JOIN sistema_salud s ON s.id_salud = t.id_salud
        ORDER BY t.nombre_completo ASC
    ")->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $accion   = $_POST['accion']  ?? 'nuevo';
        $rut      = trim($_POST['rut_trabajador']       ?? '');
        $nombre   = strtoupper(trim($_POST['nombre_completo'] ?? ''));

        // ── ELIMINAR ──
        if ($accion === 'eliminar' && $rut !== '') {
            try {
                $stmt = $pdo->prepare("DELETE FROM trabajador WHERE rut_trabajador = :rut");
                $stmt->execute([':rut' => $rut]);
                $ok    = true;
                $okMsg = 'Trabajador eliminado correctamente.';
                $todosTrabajadores = $pdo->query("
                    SELECT t.rut_trabajador, t.nombre_completo, t.sueldo_base_fijo,
                           t.id_cargo, t.id_tipo_contrato, t.id_afp, t.id_salud,
                           t.fecha_inicio_contrato, t.fecha_termino_contrato,
                           c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
                    FROM trabajador t
                    JOIN cargo c ON c.id_cargo = t.id_cargo
                    JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
                    JOIN afp a ON a.id_afp = t.id_afp
                    JOIN sistema_salud s ON s.id_salud = t.id_salud
                    ORDER BY t.nombre_completo ASC
                ")->fetchAll();
            } catch (Throwable $ex) {
                $errores[] = 'No se pudo eliminar: ' . $ex->getMessage();
            }
            goto fin_post;
        }
        $sueldo   = trim($_POST['sueldo_base_fijo']     ?? '');
        $idCargo  = (int)($_POST['id_cargo']            ?? 0);
        $idTipo   = (int)($_POST['id_tipo_contrato']    ?? 0);
        $fInicio  = trim($_POST['fecha_inicio_contrato'] ?? '');
        $fTermino = trim($_POST['fecha_termino_contrato'] ?? '');
        $idAfp    = (int)($_POST['id_afp']   ?? 0);
        $idSalud  = (int)($_POST['id_salud'] ?? 0);

        // Validaciones
        if ($rut === '') {
            $errores[] = 'El RUT es obligatorio.';
        } elseif (!preg_match('/^\d{1,2}\.\d{3}\.\d{3}-[\dkK]$/i', $rut)) {
            $errores[] = 'El RUT debe tener el formato 1.111.111-1 o 16.234.445-9.';
        } else {
            $soloDigitos = preg_replace('/[.\-]/u', '', $rut);
            if (strlen($soloDigitos) < 8 || strlen($soloDigitos) > 9)
                $errores[] = 'El RUT no es válido. Verifique la cantidad de dígitos.';
        }

        if ($nombre === '')
            $errores[] = 'El nombre completo es obligatorio.';
        elseif (!preg_match('/^[A-ZÁÉÍÓÚÜÑ\s]+$/u', $nombre))
            $errores[] = 'El nombre solo puede contener letras y espacios.';
        elseif (mb_strlen($nombre) > 50)
            $errores[] = 'El nombre no puede superar los 50 caracteres.';

        $sueldoNum = (int) preg_replace('/\D/', '', $sueldo);
        if ($sueldo === '')           $errores[] = 'El sueldo base es obligatorio.';
        elseif ($sueldoNum < 500000)  $errores[] = 'El sueldo base no puede ser menor a $500.000.';
        elseif ($sueldoNum > 8000000) $errores[] = 'El sueldo base no puede superar los $8.000.000.';

        if ($idCargo  <= 0) $errores[] = 'Seleccione un cargo.';
        if ($idTipo   <= 0) $errores[] = 'Seleccione un tipo de contrato.';
        if ($fInicio === '') $errores[] = 'La fecha de inicio es obligatoria.';
        if ($idAfp    <= 0) $errores[] = 'Seleccione una AFP.';
        if ($idSalud  <= 0) $errores[] = 'Seleccione un sistema de salud.';

        if ($fTermino !== '' && $fInicio !== '' && $fTermino <= $fInicio)
            $errores[] = 'La fecha de término debe ser posterior a la fecha de inicio.';

        if (!$errores) {
            if ($accion === 'editar') {
                $rutOriginal = trim($_POST['rut_original'] ?? $rut);

                if ($rutOriginal !== $rut) {
                    // RUT cambió: verificar que el nuevo no exista
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM trabajador WHERE rut_trabajador = :r");
                    $chk->execute([':r' => $rut]);
                    if ((int)$chk->fetchColumn() > 0) {
                        $errores[] = 'El RUT ' . $rut . ' ya existe. Ingrese uno diferente.';
                        goto fin_post;
                    }
                    // Actualizar todas las columnas incluyendo el RUT
                    $stmt = $pdo->prepare("
                        UPDATE trabajador SET
                            rut_trabajador         = :rut_nuevo,
                            nombre_completo        = :nom,
                            sueldo_base_fijo       = :sueldo,
                            id_cargo               = :cargo,
                            id_tipo_contrato       = :tipo,
                            fecha_inicio_contrato  = :fini,
                            fecha_termino_contrato = :fter,
                            id_afp                 = :afp,
                            id_salud               = :salud
                        WHERE rut_trabajador = :rut_orig
                    ");
                    $stmt->execute([
                        ':rut_nuevo' => $rut,
                        ':nom'       => $nombre, ':sueldo' => $sueldoNum,
                        ':cargo'     => $idCargo, ':tipo'  => $idTipo,
                        ':fini'      => $fInicio, ':fter'  => ($fTermino !== '' ? $fTermino : null),
                        ':afp'       => $idAfp,  ':salud'  => $idSalud,
                        ':rut_orig'  => $rutOriginal,
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE trabajador SET
                            nombre_completo        = :nom,
                            sueldo_base_fijo       = :sueldo,
                            id_cargo               = :cargo,
                            id_tipo_contrato       = :tipo,
                            fecha_inicio_contrato  = :fini,
                            fecha_termino_contrato = :fter,
                            id_afp                 = :afp,
                            id_salud               = :salud
                        WHERE rut_trabajador = :rut
                    ");
                    $stmt->execute([
                        ':nom'   => $nombre, ':sueldo' => $sueldoNum,
                        ':cargo' => $idCargo, ':tipo'  => $idTipo,
                        ':fini'  => $fInicio, ':fter'  => ($fTermino !== '' ? $fTermino : null),
                        ':afp'   => $idAfp,  ':salud'  => $idSalud, ':rut' => $rut,
                    ]);
                }
                $okMsg = 'trabajador actualizado correctamente.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO trabajador
                        (rut_trabajador, nombre_completo, sueldo_base_fijo, id_cargo,
                         id_tipo_contrato, fecha_inicio_contrato, fecha_termino_contrato, id_afp, id_salud)
                    VALUES (:rut, :nom, :sueldo, :cargo, :tipo, :fini, :fter, :afp, :salud)
                ");
                $stmt->execute([
                    ':rut'   => $rut,    ':nom'    => $nombre,  ':sueldo' => $sueldoNum,
                    ':cargo' => $idCargo, ':tipo'  => $idTipo,
                    ':fini'  => $fInicio, ':fter'  => ($fTermino !== '' ? $fTermino : null),
                    ':afp'   => $idAfp,  ':salud'  => $idSalud,
                ]);
                $okMsg = 'Trabajador agregado correctamente.';
            }
            $ok = true;

            // Recargar lista actualizada
            $todosTrabajadores = $pdo->query("
                SELECT t.rut_trabajador, t.nombre_completo, t.sueldo_base_fijo,
                       t.id_cargo, t.id_tipo_contrato, t.id_afp, t.id_salud,
                       t.fecha_inicio_contrato, t.fecha_termino_contrato,
                       c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
                FROM trabajador t
                JOIN cargo c ON c.id_cargo = t.id_cargo
                JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
                JOIN afp a ON a.id_afp = t.id_afp
                JOIN sistema_salud s ON s.id_salud = t.id_salud
                ORDER BY t.nombre_completo ASC
            ")->fetchAll();
        }
        fin_post:;
    }

} catch (Throwable $e) {
    $errores[] = 'Error de base de datos: ' . $e->getMessage();
}

function selOpt($lista, $idField, $valField, $selected = 0) {
    foreach ($lista as $row) {
        $sel = ((int)$row[$idField] === (int)$selected) ? 'selected' : '';
        echo '<option value="'.(int)$row[$idField].'" '.$sel.'>'.htmlspecialchars($row[$valField]).'</option>';
    }
}

include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/trabajadores_nuevo.css" />

<!-- Datos de trabajadores para uso en JS (JSON seguro) -->
<script>
var TRABAJADORES = <?= json_encode(array_values($todosTrabajadores), JSON_UNESCAPED_UNICODE) ?>;
var CARGOS       = <?= json_encode(array_values($cargos),            JSON_UNESCAPED_UNICODE) ?>;
var CONTRATOS    = <?= json_encode(array_values($tiposContrato),     JSON_UNESCAPED_UNICODE) ?>;
var AFPS         = <?= json_encode(array_values($afps),              JSON_UNESCAPED_UNICODE) ?>;
var SALUDES      = <?= json_encode(array_values($sistemasSalud),     JSON_UNESCAPED_UNICODE) ?>;
</script>

<section class="panel">
  <div class="tabs">
    <a class="tab" href="/remuneraciones/public/trabajadores_listado.php">Listado Trabajadores</a>
    <a class="tab active" id="tab-titulo">Nuevo trabajador</a>
  </div>

  <!-- Mensajes -->
  <?php if ($ok): ?>
    <div class="badge success" id="msg-ok" style="display:inline-block;margin-bottom:12px">
      <?= htmlspecialchars($okMsg) ?>
    </div>
  <?php endif; ?>
  <div class="badge success" id="msg-ok-js" style="display:none;margin-bottom:12px"></div>

  <?php if ($errores): ?>
    <div class="panel panel-errores" style="border-color:#3b1f1f;background:#1a0f0f;margin-bottom:12px">
      <div class="badge">Se encontraron errores</div>
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════
       BUSCADOR — botón en flujo, panel flotante fixed
  ══════════════════════════════════════════ -->
  <div class="buscador-barra">
    <span class="buscador-titulo">Buscar lista de trabajadores</span>
    <button type="button" id="btn-toggle-buscador" class="btn small">Mostrar lista</button>
  </div>

  <!-- Panel flotante: fixed, no desplaza ningún elemento -->
  <div id="panel-buscador">
    <div class="panel-buscador-header">
      <span class="buscador-titulo">Lista de trabajadores</span>
      <button type="button" id="btn-cerrar-buscador" class="btn small">✕ Cerrar</button>
    </div>
    <input type="text" id="input-buscar" placeholder="Buscar por nombre o RUT…" autocomplete="off">
    <div class="tabla-buscador-wrap">
      <table class="table" id="tabla-buscador">
        <thead>
          <tr>
            <th>RUT</th><th>Nombre</th><th>Cargo</th><th>Contrato</th><th>Sueldo base</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody-buscador">
            <?php foreach ($todosTrabajadores as $t): ?>
            <tr class="fila-t"
                data-rut="<?= htmlspecialchars($t['rut_trabajador']) ?>"
                data-nombre="<?= strtolower(htmlspecialchars($t['nombre_completo'])) ?>"
                data-rutraw="<?= strtolower(htmlspecialchars($t['rut_trabajador'])) ?>">
              <td class="col-rut"><?= htmlspecialchars($t['rut_trabajador']) ?></td>
              <td class="col-nombre"><?= htmlspecialchars($t['nombre_completo']) ?></td>
              <td class="col-cargo"><?= htmlspecialchars($t['nombre_cargo']) ?></td>
              <td class="col-contrato"><?= htmlspecialchars($t['nombre_contrato']) ?></td>
              <td class="col-sueldo">$<?= number_format((float)$t['sueldo_base_fijo'],0,',','.') ?></td>
              <td style="white-space:nowrap">
                <button type="button" class="btn small btn-cargar-edicion" data-rut="<?= htmlspecialchars($t['rut_trabajador']) ?>">
                  Editar
                </button>
                <button type="button" class="btn small btn-eliminar" 
                        data-rut="<?= htmlspecialchars($t['rut_trabajador']) ?>"
                        data-nombre="<?= htmlspecialchars($t['nombre_completo']) ?>">
                  Eliminar
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($todosTrabajadores)): ?>
              <tr><td colspan="7" class="center lead">Sin trabajadores registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <p id="sin-resultados" style="display:none;color:var(--muted);font-size:13px;margin-top:8px">Sin resultados.</p>
    </div>
  </div>
  <!-- Overlay para cerrar al hacer clic fuera -->
  <div id="buscador-overlay"></div>

  <!-- ══════════════════════════════════════════
       FORMULARIO
  ══════════════════════════════════════════ -->
  <form method="post" class="form" id="form-trabajador">
    <input type="hidden" name="accion" id="campo-accion" value="nuevo">
    <input type="hidden" name="rut_original" id="campo-rut-original" value="">

    <fieldset>
      <legend id="form-legend">Datos del trabajador</legend>

      <label>RUT
        <input type="text" id="rut_trabajador" name="rut_trabajador"
          placeholder="01.111.111-1" maxlength="12"
          value="<?= htmlspecialchars($_POST['rut_trabajador'] ?? '') ?>" required>
        <small class="campo-ayuda">Formato: 1.111.111-1 hasta 99.999.999-9</small>
        <small id="aviso-rut-editable" class="campo-ayuda" style="display:none;color:#f59e0b">
          ⚠️ Cambiar el RUT actualizará también las liquidaciones asociadas.
        </small>
      </label>

      <label>Nombre completo
        <input type="text" id="nombre_completo" name="nombre_completo"
          maxlength="50" placeholder="JUAN PÉREZ SOTO"
          style="text-transform:uppercase"
          value="<?= htmlspecialchars(strtoupper($_POST['nombre_completo'] ?? '')) ?>" required>
        <small class="campo-ayuda">Máximo 50 caracteres — solo letras y espacios</small>
      </label>

      <label>Sueldo base
        <input type="text" id="sueldo_base_fijo" name="sueldo_base_fijo"
          placeholder="$500.000 — $8.000.000"
          value="<?= htmlspecialchars($_POST['sueldo_base_fijo'] ?? '') ?>" required>
        <small class="campo-ayuda">Mínimo $500.000 — Máximo $8.000.000</small>
      </label>

      <label>Cargo
        <select name="id_cargo" id="id_cargo" required>
          <option value="">-- Seleccione --</option>
          <?php selOpt($cargos, 'id_cargo', 'nombre_cargo', $_POST['id_cargo'] ?? 0); ?>
        </select>
      </label>

      <label>Tipo de contrato
        <select name="id_tipo_contrato" id="id_tipo_contrato" required>
          <option value="">-- Seleccione --</option>
          <?php selOpt($tiposContrato, 'id_tipo_contrato', 'nombre_contrato', $_POST['id_tipo_contrato'] ?? 0); ?>
        </select>
      </label>

      <label>Fecha inicio contrato
        <input type="date" name="fecha_inicio_contrato" id="fecha_inicio_contrato"
          value="<?= htmlspecialchars($_POST['fecha_inicio_contrato'] ?? '') ?>" required>
      </label>

      <label>Fecha término contrato
        <input type="date" name="fecha_termino_contrato" id="fecha_termino_contrato"
          value="<?= htmlspecialchars($_POST['fecha_termino_contrato'] ?? '') ?>">
        <small class="campo-ayuda">Completar solo para terminar el vínculo laboral</small>
      </label>

      <label>AFP
        <select name="id_afp" id="id_afp" required>
          <option value="">-- Seleccione --</option>
          <?php selOpt($afps, 'id_afp', 'nombre_afp', $_POST['id_afp'] ?? 0); ?>
        </select>
      </label>

      <label>Sistema de salud
        <select name="id_salud" id="id_salud" required>
          <option value="">-- Seleccione --</option>
          <?php selOpt($sistemasSalud, 'id_salud', 'nombre_salud', $_POST['id_salud'] ?? 0); ?>
        </select>
      </label>
    </fieldset>

    <div class="actions">
      <button type="button" id="btn-cancelar-edicion" class="btn" style="display:none">✖ Cancelar</button>
      <button class="btn primary" type="submit" id="btn-guardar">Guardar</button>
             <a class="btn ghost  btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
    </div>
  </form>
</section>

<!-- ══════════════════════════════════════════
     MODAL CONFIRMACIÓN ELIMINAR
══════════════════════════════════════════ -->
<div id="modal-eliminar" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);align-items:center;justify-content:center">
  <div style="background:var(--panel);border:1px solid #7f1d1d;border-radius:14px;padding:28px 32px;max-width:420px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.5)">
    <h3 style="margin:0 0 8px;color:#f87171">Confirmar eliminación</h3>
    <p style="margin:0 0 20px;color:var(--txt)">
      ¿Está seguro que desea eliminar al trabajador<br>
      <strong id="modal-nombre-trabajador" style="color:#fca5a5"></strong>?<br>
      <small style="color:var(--muted)">Esta acción no se puede deshacer.</small>
    </p>
    <div style="display:flex;gap:12px;justify-content:flex-end">
      <button type="button" id="modal-btn-cancelar" class="btn">Cancelar</button>
      <button type="button" id="modal-btn-confirmar" style="background:#7f1d1d;border:1px solid #991b1b;color:#fca5a5;padding:10px 14px;border-radius:10px;cursor:pointer;font:inherit">
         Sí, eliminar
      </button>
    </div>
  </div>
</div>

<!-- Form oculto para POST eliminar -->
<form id="form-eliminar" method="post" style="display:none">
  <input type="hidden" name="accion" value="eliminar">
  <input type="hidden" name="rut_trabajador" id="eliminar-rut">
</form>



<script>
(function () {

  /* ════════════════════════════════════════
     AUTO-CARGAR si viene ?editar=RUT desde listado
  ════════════════════════════════════════ */
  (function autoCargar() {
    var params = new URLSearchParams(window.location.search);
    var rutParam = params.get('editar');
    if (!rutParam) return;
    var t = TRABAJADORES.find(function (w) { return w.rut_trabajador === rutParam; });
    if (!t) return;
    // Abrir buscador y marcar fila
    var panel = document.getElementById('panel-buscador');
    var btnToggle = document.getElementById('btn-toggle-buscador');
    if (panel) { abrirBuscador(); }
    // Simular clic en el botón editar de esa fila
    var btnFila = document.querySelector('.btn-cargar-edicion[data-rut="' + rutParam + '"]');
    if (btnFila) btnFila.click();
  })();

  /* ════════════════════════════════════════
     REFS
  ════════════════════════════════════════ */
  var rutInput      = document.getElementById('rut_trabajador');
  var nombreInput   = document.getElementById('nombre_completo');
  var sueldoInput   = document.getElementById('sueldo_base_fijo');
  var fInicioInput  = document.getElementById('fecha_inicio_contrato');
  var fTerminoInput = document.getElementById('fecha_termino_contrato');
  var selCargo      = document.getElementById('id_cargo');
  var selTipo       = document.getElementById('id_tipo_contrato');
  var selAfp        = document.getElementById('id_afp');
  var selSalud      = document.getElementById('id_salud');
  var campoAccion   = document.getElementById('campo-accion');
  var btnGuardar    = document.getElementById('btn-guardar');
  var btnCancelar   = document.getElementById('btn-cancelar-edicion');
  var formLegend    = document.getElementById('form-legend');
  var tabTitulo     = document.getElementById('tab-titulo');
  var msgOkJs       = document.getElementById('msg-ok-js');

  /* ════════════════════════════════════════
     TOGGLE BUSCADOR
  ════════════════════════════════════════ */
  var btnToggle   = document.getElementById('btn-toggle-buscador');
  var panelBuscar = document.getElementById('panel-buscador');

  var overlay    = document.getElementById('buscador-overlay');
  var btnCerrar  = document.getElementById('btn-cerrar-buscador');

  function abrirBuscador() {
    panelBuscar.classList.add('abierto');
    overlay.classList.add('abierto');
    btnToggle.textContent = 'Mostrar lista';
    document.getElementById('input-buscar').focus();
  }

  function cerrarBuscador() {
    panelBuscar.classList.remove('abierto');
    overlay.classList.remove('abierto');
    btnToggle.textContent = 'Mostrar lista';
  }

  btnToggle.addEventListener('click', function () {
    panelBuscar.classList.contains('abierto') ? cerrarBuscador() : abrirBuscador();
  });

  btnCerrar.addEventListener('click', cerrarBuscador);
  overlay.addEventListener('click',   cerrarBuscador);

  /* ════════════════════════════════════════
     FILTRO EN TIEMPO REAL
  ════════════════════════════════════════ */
  var inputBuscar   = document.getElementById('input-buscar');
  var sinResultados = document.getElementById('sin-resultados');

  inputBuscar.addEventListener('input', function () {
    var q       = this.value.toLowerCase().trim();
    var visibles = 0;
    document.querySelectorAll('.fila-t').forEach(function (fila) {
      var match = fila.dataset.nombre.includes(q) || fila.dataset.rutraw.includes(q);
      fila.classList.toggle('oculta', !match);
      if (match) visibles++;
    });
    sinResultados.style.display = (visibles === 0 && q !== '') ? 'block' : 'none';
  });

  /* ════════════════════════════════════════
     CARGAR DATOS EN FORMULARIO AL PRESIONAR ✏️ EDITAR
  ════════════════════════════════════════ */
  document.getElementById('tbody-buscador').addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-cargar-edicion');
    if (!btn) return;

    var rut = btn.dataset.rut;
    var t   = TRABAJADORES.find(function (w) { return w.rut_trabajador === rut; });
    if (!t) return;

    // Resaltar fila seleccionada
    document.querySelectorAll('.fila-t').forEach(function (f) { f.classList.remove('seleccionada'); });
    btn.closest('.fila-t').classList.add('seleccionada');

    // ── Llenar formulario ──
    rutInput.value      = t.rut_trabajador;
    rutInput.readOnly   = false;
    rutInput.style.opacity = '1';
    rutInput.style.cursor  = '';
    document.getElementById('campo-rut-original').value = '';
    document.getElementById('aviso-rut-editable').style.display = 'none';
    // Guardar RUT original para detectar si cambió
    document.getElementById('campo-rut-original').value = t.rut_trabajador;
    document.getElementById('aviso-rut-editable').style.display = 'block';

    nombreInput.value   = t.nombre_completo;
    sueldoInput.value   = parseInt(t.sueldo_base_fijo, 10).toLocaleString('es-CL');
    fInicioInput.value  = t.fecha_inicio_contrato  || '';
    fTerminoInput.value = t.fecha_termino_contrato || '';

    setSelect(selCargo, t.id_cargo);
    setSelect(selTipo,  t.id_tipo_contrato);
    setSelect(selAfp,   t.id_afp);
    setSelect(selSalud, t.id_salud);

    // Modo edición
    campoAccion.value   = 'editar';
    btnGuardar.textContent = 'Actualizar';
    formLegend.textContent = 'Editando: ' + t.nombre_completo;
    tabTitulo.textContent  = 'Editar trabajador';
    btnCancelar.style.display = 'inline-flex';

    // Animar campos para que el usuario note que se llenaron
    [rutInput, nombreInput, sueldoInput, selCargo, selTipo,
     selAfp, selSalud, fInicioInput, fTerminoInput].forEach(function (el) {
      el.classList.remove('campo-destacado');
      void el.offsetWidth; // reflow para reiniciar animación
      el.classList.add('campo-destacado');
    });

    // Cerrar buscador y scroll al formulario
    cerrarBuscador();
    document.getElementById('form-trabajador').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  /* ════════════════════════════════════════
     ELIMINAR — modal de confirmación
  ════════════════════════════════════════ */
  var modal         = document.getElementById('modal-eliminar');
  var modalNombre   = document.getElementById('modal-nombre-trabajador');
  var modalCancelar = document.getElementById('modal-btn-cancelar');
  var modalConfirmar= document.getElementById('modal-btn-confirmar');
  var formEliminar  = document.getElementById('form-eliminar');
  var eliminarRutInput = document.getElementById('eliminar-rut');

  // Abrir modal al hacer clic en ELIMINAR
  document.getElementById('tbody-buscador').addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-eliminar');
    if (!btn) return;
    eliminarRutInput.value  = btn.dataset.rut;
    modalNombre.textContent = btn.dataset.nombre + ' (' + btn.dataset.rut + ')';
    modal.style.display     = 'flex';
  });

  // Cerrar modal
  modalCancelar.addEventListener('click', function () {
    modal.style.display = 'none';
  });
  modal.addEventListener('click', function (e) {
    if (e.target === modal) modal.style.display = 'none';
  });

  // Confirmar → enviar form POST
  modalConfirmar.addEventListener('click', function () {
    modal.style.display = 'none';
    formEliminar.submit();
  });

  /* ════════════════════════════════════════
     CANCELAR EDICIÓN → LIMPIAR FORMULARIO
  ════════════════════════════════════════ */
  btnCancelar.addEventListener('click', function () {
    limpiarFormulario();
    document.querySelectorAll('.fila-t').forEach(function (f) { f.classList.remove('seleccionada'); });
  });

  /* ════════════════════════════════════════
     POST exitoso → actualizar fila en buscador sin recargar
  ════════════════════════════════════════ */
  <?php if ($ok): ?>
  (function () {
    // El servidor ya guardó. Actualizamos la tabla del buscador en el cliente.
    var datos = TRABAJADORES; // ya tiene los datos actualizados (PHP los recargó)
    var tbody = document.getElementById('tbody-buscador');

    // Reconstruir filas
    tbody.innerHTML = '';
    if (datos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="center lead">Sin trabajadores registrados.</td></tr>';
    } else {
      datos.forEach(function (t) {
        var sueldo = parseInt(t.sueldo_base_fijo, 10).toLocaleString('es-CL');
        var tr = document.createElement('tr');
        tr.className   = 'fila-t';
        tr.dataset.rut     = t.rut_trabajador;
        tr.dataset.nombre  = t.nombre_completo.toLowerCase();
        tr.dataset.rutraw  = t.rut_trabajador.toLowerCase();
        tr.innerHTML =
          '<td class="col-rut">'      + esc(t.rut_trabajador)   + '</td>' +
          '<td class="col-nombre">'   + esc(t.nombre_completo)  + '</td>' +
          '<td class="col-cargo">'    + esc(t.nombre_cargo)     + '</td>' +
          '<td class="col-contrato">' + esc(t.nombre_contrato)  + '</td>' +
          '<td class="col-sueldo">$'  + sueldo                  + '</td>' +
          '<td style="white-space:nowrap">' +
            '<button type="button" class="btn small btn-cargar-edicion" data-rut="' + esc(t.rut_trabajador) + '">✏️ Editar</button> ' +
            '<button type="button" class="btn small btn-eliminar" style="background:#7f1d1d;border-color:#991b1b;color:#fca5a5;margin-left:4px" data-rut="' + esc(t.rut_trabajador) + '" data-nombre="' + esc(t.nombre_completo) + '">🗑️ Eliminar</button>' +
          '</td>';
        tbody.appendChild(tr);
      });
    }

    // Mostrar mensaje y limpiar formulario
    msgOkJs.textContent = '<?= addslashes($okMsg) ?>';
    msgOkJs.style.display = 'inline-block';
    setTimeout(function () { msgOkJs.style.display = 'none'; }, 4000);

    limpiarFormulario();
  })();
  <?php endif; ?>

  /* ════════════════════════════════════════
     HELPERS
  ════════════════════════════════════════ */
  function setSelect(sel, val) {
    for (var i = 0; i < sel.options.length; i++) {
      if (parseInt(sel.options[i].value, 10) === parseInt(val, 10)) {
        sel.selectedIndex = i; return;
      }
    }
  }

  function limpiarFormulario() {
    rutInput.value      = '';
    rutInput.readOnly   = false;
    rutInput.style.opacity = '1';
    rutInput.style.cursor  = '';
    nombreInput.value   = '';
    sueldoInput.value   = '';
    fInicioInput.value  = '';
    fTerminoInput.value = '';
    selCargo.selectedIndex = 0;
    selTipo.selectedIndex  = 0;
    selAfp.selectedIndex   = 0;
    selSalud.selectedIndex = 0;
    campoAccion.value      = 'nuevo';
    btnGuardar.textContent = 'Guardar';
    formLegend.textContent = 'Datos del trabajador';
    tabTitulo.textContent  = 'Nuevo trabajador';
    btnCancelar.style.display = 'none';
    document.querySelectorAll('.input-error').forEach(function (el) { el.classList.remove('input-error'); });
    document.querySelectorAll('.msg-error-campo').forEach(function (el) { el.remove(); });
  }

  function esc(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ════════════════════════════════════════
     VALIDACIONES EN TIEMPO REAL
  ════════════════════════════════════════ */

  // RUT
  rutInput.addEventListener('keypress', function (e) {
    if (this.readOnly) return;
    if (!/[0-9]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key))
      e.preventDefault();
  });
  rutInput.addEventListener('input', function () {
    if (this.readOnly) return;
    var cursor  = this.selectionStart;
    var prevLen = this.value.length;
    var raw = this.value.replace(/[^0-9kK]/gi,'').toUpperCase();
    if (raw.length > 9) raw = raw.slice(0,9);
    if (!raw) { this.value = ''; return; }
    var dv = raw.slice(-1), body = raw.slice(0,-1);
    var fmt = body.length <= 3 ? body
            : body.length <= 6 ? body.slice(0,-3)+'.'+body.slice(-3)
            : body.slice(0,-6)+'.'+body.slice(-6,-3)+'.'+body.slice(-3);
    this.value = fmt + '-' + dv;
    var diff = this.value.length - prevLen;
    this.setSelectionRange(cursor+diff, cursor+diff);
  });
  rutInput.addEventListener('blur', function () {
    if (this.readOnly) return;
    var ok = /^\d{1,2}\.\d{3}\.\d{3}-[\dkK]$/i.test(this.value);
    toggleError(this, ok ? null : 'Formato inválido. Ej: 1.111.111-1');
  });

  // Nombre
  nombreInput.addEventListener('input', function () {
    var pos = this.selectionStart;
    this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÜÑ\s]/gi,'');
    this.setSelectionRange(pos, pos);
  });
  nombreInput.addEventListener('blur', function () {
    var len = this.value.trim().length;
    toggleError(this, len===0 ? 'Obligatorio.' : len>50 ? 'Máximo 50 caracteres.' : null);
  });

  // Sueldo
  sueldoInput.addEventListener('keypress', function (e) {
    if (!/[0-9]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key))
      e.preventDefault();
  });
  sueldoInput.addEventListener('input', function () {
    var raw = this.value.replace(/\D/g,'');
    this.value = raw ? parseInt(raw,10).toLocaleString('es-CL') : '';
  });
  sueldoInput.addEventListener('blur', function () {
    var n = parseInt(this.value.replace(/\D/g,'')||'0',10);
    toggleError(this, n<500000 ? 'Mínimo $500.000.' : n>8000000 ? 'Máximo $8.000.000.' : null);
  });

  function toggleError(input, msg) {
    var span = input.parentNode.querySelector('.msg-error-campo');
    if (msg) {
      input.classList.add('input-error');
      if (!span) { span = document.createElement('span'); span.className='msg-error-campo'; input.parentNode.appendChild(span); }
      span.textContent = msg;
    } else {
      input.classList.remove('input-error');
      if (span) span.remove();
    }
  }

})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>