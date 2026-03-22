<?php
// public/liquidaciones_nueva.php
require_once __DIR__ . '/../src/Db.php';

$pageTitle = 'Nueva liquidación';
$errores   = [];

try {
    $pdo = (new Db())->pdo();

    // Todos los trabajadores con sus datos completos para el JS
    $trabajadores = $pdo->query("
        SELECT t.rut_trabajador, t.nombre_completo, t.sueldo_base_fijo,
               c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
        FROM trabajador t
        JOIN cargo c           ON c.id_cargo          = t.id_cargo
        JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
        JOIN afp a             ON a.id_afp            = t.id_afp
        JOIN sistema_salud s   ON s.id_salud          = t.id_salud
        ORDER BY t.nombre_completo
    ")->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {

        $rut      = trim($_POST['rut_trabajador']  ?? '');
        $mes      = (int)($_POST['mes_periodo']    ?? 0);
        $anio     = (int)($_POST['anio_periodo']   ?? 0);
        $dias     = ($_POST['dias_trabajados'] !== '') ? (int)$_POST['dias_trabajados']                   : null;
        $valor_uf = ($_POST['valor_uf']        !== '') ? (float)$_POST['valor_uf']                        : null;
        $sueldo   = ($_POST['sueldo_base_mes'] !== '') ? (float)$_POST['sueldo_base_mes']                 : null;
        $grat     = ($_POST['gratificacion']   !== '') ? (float)$_POST['gratificacion']                   : null;
        $colacion = ($_POST['colacion']        !== '') ? (float)$_POST['colacion']                        : null;
        $cotPrev  = ($_POST['cotiz_previsional_obligatoria'] !== '') ? (float)$_POST['cotiz_previsional_obligatoria'] : null;
        $cotSalud = ($_POST['cotiz_salud_obligatoria']       !== '') ? (float)$_POST['cotiz_salud_obligatoria']       : null;
        $segCes   = ($_POST['seguro_cesantia']               !== '') ? (float)$_POST['seguro_cesantia']               : null;
        $impPrev  = ($_POST['imp_prev_salud']                !== '') ? (float)$_POST['imp_prev_salud']                : null;
        $otros    = ($_POST['otros_descuentos']              !== '') ? (float)$_POST['otros_descuentos']              : null;
        $base     = ($_POST['base_tributable']               !== '') ? (float)$_POST['base_tributable']               : null;
        $liquido  = ($_POST['liquido_a_pagar']               !== '') ? (float)$_POST['liquido_a_pagar']               : null;

        if ($rut === '')            $errores[] = 'Seleccione un trabajador.';
        if ($mes < 1 || $mes > 12) $errores[] = 'Mes inválido.';
        if ($anio < 2000)          $errores[] = 'Año inválido.';
        if ($liquido === null)      $errores[] = 'Debe indicar el líquido a pagar.';

        if (!$errores) {
            $stmt = $pdo->prepare("
                INSERT INTO liquidacion
                    (rut_trabajador, mes_periodo, anio_periodo, nombre_empleador,
                     dias_trabajados, valor_uf, sueldo_base_mes, gratificacion,
                     colacion, cotiz_previsional_obligatoria, cotiz_salud_obligatoria,
                     seguro_cesantia, otros_descuentos, imp_prev_salud, imp_cesantia,
                     base_tributable, liquido_a_pagar)
                VALUES
                    (:rut, :mes, :anio, :empleador,
                     :dias, :uf, :sueldo, :grat,
                     :col, :prev, :sal,
                     :ces, :otros, :impPrev, NULL,
                     :base, :liq)
            ");
            $stmt->execute([
                ':rut'      => $rut,    ':mes'   => $mes,      ':anio'    => $anio,
                ':empleador'=> 'Colegio Ejemplo',
                ':dias'     => $dias,   ':uf'    => $valor_uf,
                ':sueldo'   => $sueldo, ':grat'  => $grat,     ':col'     => $colacion,
                ':prev'     => $cotPrev,':sal'   => $cotSalud, ':ces'     => $segCes,
                ':otros'    => $otros,  ':impPrev'=> $impPrev,
                ':base'     => $base,   ':liq'   => $liquido,
            ]);
            $lastId = (int)$pdo->lastInsertId();
            header('Location: /remuneraciones/public/liquidacion_ver.php?id=' . $lastId);
            exit;
        }
    }

} catch (Throwable $e) {
    $errores[] = 'Error: ' . $e->getMessage();
}

include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/liquidaciones_nuevas.css" />

<!-- JSON con datos de trabajadores para JS (sin recarga de página) -->
<script>
var TRABAJADORES_LIQ = <?= json_encode(array_values($trabajadores), JSON_UNESCAPED_UNICODE) ?>;
</script>

<section class="panel">
  <div class="tabs">
    <a class="tab" href="/remuneraciones/public/listado_liquidaciones.php">Listado Liquidaciones</a>
    <span class="tab active">Crear Nueva Liquidación</span>
  </div>

  <?php if ($errores): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f;color:#fff;margin-bottom:12px">
      <div class="badge" style="background:#7f1d1d;border-color:#991b1b;color:#fff">Errores</div>
      <ul style="color:#fff"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form" action="/remuneraciones/public/liquidaciones_nueva.php">

    <!-- ===== ENCABEZADO ===== -->
    <fieldset>
      <legend>Identificación de la liquidación</legend>

      <label>Trabajador
        <select name="rut_trabajador" id="sel-trabajador" required>
          <option value="">-- Seleccione un trabajador --</option>
          <?php foreach ($trabajadores as $t): ?>
            <option value="<?= htmlspecialchars($t['rut_trabajador']) ?>"
              <?= ($t['rut_trabajador'] === ($_POST['rut_trabajador'] ?? '')) ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['nombre_completo']) ?> (<?= htmlspecialchars($t['rut_trabajador']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <div class="flex">
        <div class="flex-1">
          <label>Empresa
            <input type="text" value="Colegio Ejemplo" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Tipo de contrato
            <input type="text" id="campo-contrato" placeholder="— seleccione trabajador —" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Nombre completo
            <input type="text" id="campo-nombre" placeholder="— seleccione trabajador —" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>RUT
            <input type="text" id="campo-rut" placeholder="— seleccione trabajador —" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Cargo
            <input type="text" id="campo-cargo" placeholder="— seleccione trabajador —" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Días trabajados
            <select name="dias_trabajados" id="campo-dias" required>
              <option value="">--</option>
              <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>"
                  <?= (isset($_POST['dias_trabajados']) && (int)$_POST['dias_trabajados'] === $d) ? 'selected' : '' ?>>
                  <?= $d ?>
                </option>
              <?php endfor; ?>
            </select>
            <small style="font-size:11px;color:var(--muted)">Se selecciona el último día del mes automáticamente</small>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>AFP
            <input type="text" id="campo-afp" placeholder="— seleccione trabajador —" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Salud
            <input type="text" id="campo-salud" placeholder="— seleccione trabajador —" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Mes
            <select name="mes_periodo" id="sel-mes" required>
              <option value="">--</option>
              <?php
              $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
              for ($m = 1; $m <= 12; $m++):
                $sel = (isset($_POST['mes_periodo']) && (int)$_POST['mes_periodo'] === $m) ? 'selected' : '';
              ?>
                <option value="<?= $m ?>" <?= $sel ?>><?= $m ?> — <?= $meses[$m-1] ?></option>
              <?php endfor; ?>
            </select>
          </label>
        </div>
        <div class="flex-1">
          <label>Año
            <input type="number" name="anio_periodo" id="campo-anio"
                   min="2000" max="2099"
                   value="<?= htmlspecialchars($_POST['anio_periodo'] ?? date('Y')) ?>"
                   required>
          </label>
        </div>
      </div>

    </fieldset>

    <!-- ===== HABERES y DESCUENTOS ===== -->
    <div class="flex">
      <div class="flex-1">
        <fieldset>
          <legend>HABERES</legend>
          <table class="table">
            <tbody>
              <tr>
                <td>Sueldo base mes</td>
                <td><input type="number" step="0.01" name="sueldo_base_mes" id="campo-sueldo"
                           value="<?= htmlspecialchars($_POST['sueldo_base_mes'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Gratificación</td>
                <td><input type="number" step="0.01" name="gratificacion"
                           value="<?= htmlspecialchars($_POST['gratificacion'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Colación / Movilización (no imponible)</td>
                <td><input type="number" step="0.01" name="colacion"
                           value="<?= htmlspecialchars($_POST['colacion'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Valor UF</td>
                <td><input type="number" step="0.01" name="valor_uf"
                           value="<?= htmlspecialchars($_POST['valor_uf'] ?? '') ?>"></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>

      <div class="flex-1">
        <fieldset>
          <legend>DESCUENTOS</legend>
          <table class="table">
            <tbody>
              <tr>
                <td>Cotiz. previsional obligatoria <span class="badge">10% IT</span></td>
                <td><input type="number" step="0.01" name="cotiz_previsional_obligatoria" readonly></td>
              </tr>
              <tr>
                <td>Cotiz. salud obligatoria <span class="badge">7% IT</span></td>
                <td><input type="number" step="0.01" name="cotiz_salud_obligatoria" readonly></td>
              </tr>
              <tr>
                <td>Seguro cesantía <span class="badge">0,60% IT</span></td>
                <td><input type="number" step="0.01" name="seguro_cesantia" readonly></td>
              </tr>
              <tr>
                <td>Impuesto previsión</td>
                <td><input type="number" step="0.01" name="imp_prev_salud"
                           value="<?= htmlspecialchars($_POST['imp_prev_salud'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Otros descuentos (voluntarios)</td>
                <td><input type="number" step="0.01" name="otros_descuentos"
                           value="<?= htmlspecialchars($_POST['otros_descuentos'] ?? '') ?>"></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
    </div>

    <!-- ===== TOTALES ===== -->
    <fieldset>
      <legend>Totales por tipo</legend>
      <div class="flex">
        <div class="flex-1">
          <table class="table">
            <tbody>
              <tr>
                <td><strong>(IT)</strong> Imponible tributable</td>
                <td><input type="number" step="0.01" id="it_imponible"    name="it_imponible"    readonly></td>
              </tr>
              <tr>
                <td><strong>(NN)</strong> No imponible y no tributable</td>
                <td><input type="number" step="0.01" id="nn_no_imponible" name="nn_no_imponible" readonly></td>
              </tr>
              <tr>
                <td><strong>TOTAL HABERES</strong></td>
                <td><input type="number" step="0.01" id="total_haberes"   name="total_haberes"   readonly></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex-1">
          <table class="table">
            <tbody>
              <tr>
                <td><strong>(DL)</strong> Descuentos legales</td>
                <td><input type="number" step="0.01" id="dl_legales"       name="dl_legales"       readonly></td>
              </tr>
              <tr>
                <td><strong>(DV)</strong> Descuentos voluntarios</td>
                <td><input type="number" step="0.01" id="dv_voluntarios"   name="dv_voluntarios"   readonly></td>
              </tr>
              <tr>
                <td><strong>TOTAL DESCUENTOS</strong></td>
                <td><input type="number" step="0.01" id="total_descuentos" name="total_descuentos"  readonly></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </fieldset>

    <!-- ===== RESUMEN ===== -->
    <fieldset>
      <legend>Resumen</legend>
      <div class="flex">
        <div class="flex-1">
          <label>Líquido a pagar (obligatorio)
            <input type="number" step="0.01" name="liquido_a_pagar" required
                   value="<?= htmlspecialchars($_POST['liquido_a_pagar'] ?? '') ?>">
          </label>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn azul" name="guardar" type="submit">Guardar liquidación</button>
      <a class="btn ghost  btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
    </div>

  </form>
</section>

<script>
(function () {

  const selTrabajador = document.getElementById('sel-trabajador');
  const selMes        = document.getElementById('sel-mes');
  const campoAnio     = document.getElementById('campo-anio');
  const campoDias     = document.getElementById('campo-dias');
  const campoNombre   = document.getElementById('campo-nombre');
  const campoRut      = document.getElementById('campo-rut');
  const campoCargo    = document.getElementById('campo-cargo');
  const campoContrato = document.getElementById('campo-contrato');
  const campoAfp      = document.getElementById('campo-afp');
  const campoSalud    = document.getElementById('campo-salud');
  const campoSueldo   = document.getElementById('campo-sueldo');

  const q   = s => document.querySelector(s);
  const num = el => { if (!el) return 0; const v = parseFloat((el.value || '').replace(',','.')); return isNaN(v) ? 0 : v; };
  const set = (el, val) => { if (el) el.value = (Math.round((val + Number.EPSILON) * 100) / 100).toFixed(2); };

  /* ── 1. Cargar datos del trabajador al seleccionar ── */
  function cargarTrabajador() {
    const rut = selTrabajador.value;
    const t   = TRABAJADORES_LIQ.find(w => w.rut_trabajador === rut);

    if (!t) {
      [campoNombre, campoRut, campoCargo, campoContrato, campoAfp, campoSalud].forEach(c => c.value = '');
      campoSueldo.value = '';
      recalc();
      return;
    }

    campoNombre.value   = t.nombre_completo;
    campoRut.value      = t.rut_trabajador;
    campoCargo.value    = t.nombre_cargo;
    campoContrato.value = t.nombre_contrato;
    campoAfp.value      = t.nombre_afp;
    campoSalud.value    = t.nombre_salud;

    // Precargar sueldo base del trabajador
    campoSueldo.value = parseFloat(t.sueldo_base_fijo).toFixed(2);
    recalc();
  }

  /* ── 2. Seleccionar el último día del mes en el desplegable ── */
  function actualizarDias() {
    const mes  = parseInt(selMes.value,    10);
    const anio = parseInt(campoAnio.value, 10);
    if (!mes || !anio || anio < 2000) return;
    // new Date(anio, mes, 0) → último día del mes
    const ultimoDia = new Date(anio, mes, 0).getDate();
    campoDias.value = ultimoDia; // selecciona la opción correspondiente en el <select>
  }

  /* ── 3. Recalcular totales automáticamente ── */
  const $sueldo = q('input[name="sueldo_base_mes"]');
  const $grat   = q('input[name="gratificacion"]');
  const $cola   = q('input[name="colacion"]');
  const $prev   = q('input[name="cotiz_previsional_obligatoria"]');
  const $salud  = q('input[name="cotiz_salud_obligatoria"]');
  const $ces    = q('input[name="seguro_cesantia"]');
  const $impPS  = q('input[name="imp_prev_salud"]');
  const $otros  = q('input[name="otros_descuentos"]');
  const $it     = q('#it_imponible');
  const $nn     = q('#nn_no_imponible');
  const $th     = q('#total_haberes');
  const $dl     = q('#dl_legales');
  const $dv     = q('#dv_voluntarios');
  const $td     = q('#total_descuentos');
  const $liq    = q('input[name="liquido_a_pagar"]');

  function recalc() {
    const IT       = num($sueldo) + num($grat);
    const NN       = num($cola);
    const cotPrev  = IT * 0.10;
    const cotSalud = IT * 0.07;
    const cesantia = IT * 0.006;

    set($prev,  cotPrev);
    set($salud, cotSalud);
    set($ces,   cesantia);

    const totalHaberes = IT + NN;
    const DL = cotPrev + cotSalud + cesantia + num($impPS);
    const DV = num($otros);
    const totalDesc  = DL + DV;
    const liquidoSug = totalHaberes - totalDesc;

    set($it, IT);  set($nn, NN);  set($th, totalHaberes);
    set($dl, DL);  set($dv, DV);  set($td, totalDesc);

    if ($liq && ($liq.value === '' || $liq.dataset.autofill === '1')) {
      $liq.value = liquidoSug.toFixed(2);
      $liq.dataset.autofill = '1';
    }
  }

  /* ── Eventos ── */
  selTrabajador.addEventListener('change', cargarTrabajador);
  selMes.addEventListener('change',        actualizarDias);
  campoAnio.addEventListener('input',      actualizarDias);

  [$sueldo, $grat, $cola, $impPS, $otros].forEach(el => el && el.addEventListener('input', recalc));
  if ($liq) $liq.addEventListener('input', () => $liq.dataset.autofill = '0');

  /* ── Inicializar al cargar (en caso de POST con errores) ── */
  if (selTrabajador.value) cargarTrabajador();
  actualizarDias();
  recalc();

})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>