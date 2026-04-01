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
        t.colacion, t.transporte,
               c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
        FROM trabajador t
        JOIN cargo c           ON c.id_cargo          = t.id_cargo
        JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
        JOIN afp a             ON a.id_afp            = t.id_afp
        JOIN sistema_salud s   ON s.id_salud          = t.id_salud
        ORDER BY t.nombre_completo
    ")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {

    // Función para limpiar formato $1.000.000 → número
    $limpiar = fn($campo) => isset($_POST[$campo]) && $_POST[$campo] !== ''
        ? (float) str_replace(['$', '.', ' '], '', $_POST[$campo])
        : null;

    $rut      = trim($_POST['rut_trabajador'] ?? '');
    $mes      = (int)($_POST['mes_periodo']   ?? 0);
    $anio     = (int)($_POST['anio_periodo']  ?? 0);
    $dias     = (int)($_POST['dias_trabajados'] ?? 30);

    $sueldo   = $limpiar('sueldo_base_mes');
    $grat     = $limpiar('gratificacion');
    $colacion = $limpiar('colacion');
    $transp   = $limpiar('transporte');
    $cotPrev  = $limpiar('cotiz_previsional_obligatoria');
    $cotSalud = $limpiar('cotiz_salud_obligatoria');
    $segCes   = $limpiar('seguro_cesantia');
    $liquido  = $limpiar('liquido_a_pagar');

    if ($rut === '')            $errores[] = 'Seleccione un trabajador.';
    if ($mes < 1 || $mes > 12) $errores[] = 'Mes inválido.';
    if ($anio < 2000)          $errores[] = 'Año inválido.';
    if ($liquido === null)      $errores[] = 'Debe indicar el líquido a pagar.';

    if (!$errores) {
        $stmt = $pdo->prepare("
            INSERT INTO liquidacion
                (rut_trabajador, mes_periodo, anio_periodo, nombre_empleador,
                 dias_trabajados, sueldo_base_mes, gratificacion,
                 colacion, transporte, cotiz_previsional, cotiz_salud,
                 seguro_cesantia, liquido_a_pagar)
            VALUES
                (:rut, :mes, :anio, 'Colegio Ejemplo',
                 :dias, :sueldo, :grat,
                 :col, :trans, :prev, :sal,
                 :ces, :liq)
        ");
        $stmt->execute([
            ':rut'    => $rut,
            ':mes'    => $mes,
            ':anio'   => $anio,
            ':dias'   => $dias,
            ':sueldo' => $sueldo,
            ':grat'   => $grat,
            ':col'    => $colacion,
            ':trans'  => $transp,
            ':prev'   => $cotPrev,
            ':sal'    => $cotSalud,
            ':ces'    => $segCes,
            ':liq'    => $liquido,
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
            <input type="text" id="campo-salud-display" placeholder="— seleccione trabajador —" disabled>
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
        <table class="table">
            <tbody>
                <tr class="titulos-fijos">
                    <td colspan="2" style="text-align: center; font-weight: bold;">HABERES IMPONIBLES</td>
                </tr>
                <tr>
                    <td>Sueldo base</td>
                    <td>
                        <!-- type="text" para aceptar formato $1.000.000 -->
                        <input type="text" name="sueldo_base_mes" id="campo-sueldo"
                               value="<?= htmlspecialchars($_POST['sueldo_base_mes'] ?? '') ?>">
                    </td>
                </tr>
                <tr>
                    <td>Gratificación (25% c/tope)</td>
                    <td>
                        <input type="text" name="gratificacion" id="campo-gratificacion"
                               value="<?= htmlspecialchars($_POST['gratificacion'] ?? '') ?>" readonly>
                    </td>
                </tr>

                <tr class="titulos-fijos">
                    <td colspan="2" style="text-align: center; font-weight: bold;">HABERES NO IMPONIBLES</td>
                </tr>
                <tr>
                    <td>Colación</td>
                    <td>
                        <input type="text" name="colacion" id="campo-colacion"
                               value="<?= htmlspecialchars($_POST['colacion'] ?? '') ?>">
                    </td>
                </tr>
                <tr>
                    <td>Movilización</td>
                    <td>
                        <input type="text" name="transporte" id="campo-transporte"
                               value="<?= htmlspecialchars($_POST['transporte'] ?? '') ?>">
                    </td>
                </tr>

                <tr class="total-fijos">
                    <td><strong>TOTAL HABERES</strong></td>
                    <td>
                        <input type="text" id="total_haberes" name="total_haberes" readonly>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>

<div class="flex-1">
    <fieldset>
        <table class="table">
            <tbody>
                <tr class="titulos-fijos">
                    <td colspan="2" style="text-align: center; font-weight: bold;">DESCUENTOS LEGALES</td>
                </tr>
                <tr>
                    <td>Cotiz. previsional obligatoria (AFP)<span class="badge">10% IT</span></td>
                    <td><input type="text" name="cotiz_previsional_obligatoria" id="campo-prev" readonly></td>
                </tr>
                <tr>
                    <td>Cotiz. salud obligatoria <span class="badge">7% IT</span></td>
                    <td><input type="text" name="cotiz_salud_obligatoria" id="campo-salud" readonly></td>
                </tr>
                <tr>
                    <td>Seguro cesantía <span class="badge">0,60% IT</span></td>
                    <td><input type="text" name="seguro_cesantia" id="campo-cesantia" readonly></td>
                </tr>

                <tr class="titulos-fijos">
                    <td colspan="2" style="text-align: center; font-weight: bold;">OTROS DESCUENTOS</td>
                </tr>
                <tr>
                    <td>Otros descuentos</td>
                    <td>
                        <input type="text" name="otros_descuentos" id="campo-otros"
                               value="<?= htmlspecialchars($_POST['otros_descuentos'] ?? '') ?>">
                    </td>
                </tr>

                <tr class="total-fijos">
                    <td><strong>TOTAL DESCUENTOS</strong></td>
                    <td>
                        <input type="text" id="total_descuentos" name="total_descuentos" readonly>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>
    </div>

    <!-- ===== RESUMEN ===== -->
    <fieldset>
      <div class="flex">
        <div class="flex-1">
          <label>LIQUIDO A RECIBIR
            <input type="text" name="liquido_a_pagar" id="campo-liquido" required
                   value="<?= htmlspecialchars($_POST['liquido_a_pagar'] ?? '') ?>">
          </label>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn azul" name="guardar" type="submit">Guardar liquidación</button>
      <a class="btn ghost btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
    </div>

    <H4>Certifico que he recibido de del colegio a mi entera satisfacción el saldo indicado en la presente Liquidación y no tengo cargo ni cobro
     posterior que hacer</H4>
     <br><br><br>________________________
     <h3>FIRMA CONFORME</h3>
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
  // campo-salud-display es el input visible de Salud en el encabezado
  const campoSaludDisplay = document.getElementById('campo-salud-display');
  const campoSueldo   = document.getElementById('campo-sueldo');

  const q = s => document.querySelector(s);

  /* ── Formato CLP: $1.000.000 ── */
  const formatCLP = n => '$' + Math.round(n).toLocaleString('es-CL');

  /* ── Leer valor de un input con formato CLP o número plano ── */
  const num = el => {
    if (!el) return 0;
    // Elimina $, puntos de miles; reemplaza coma decimal por punto
    const v = parseFloat((el.value || '').replace(/\$|\./g, '').replace(',', '.'));
    return isNaN(v) ? 0 : v;
  };

  /* ── Escribir valor formateado en un input ── */
  const set = (el, val) => {
    if (el) el.value = formatCLP(val);
  };

  /* ── Formatear al salir del campo (blur) ── */
  const bindFmt = el => {
    if (!el) return;
    el.addEventListener('focus', () => {
      // Al enfocar: mostrar solo el número para editar cómodamente
      const raw = num(el);
      el.value = raw === 0 ? '' : String(Math.round(raw));
    });
    el.addEventListener('blur', () => {
      const raw = num(el);
      if (!isNaN(raw) && el.value !== '') {
        el.value = formatCLP(raw);
      }
      recalc();
    });
  };

  /* ── Referencias a los inputs de montos ── */
  const $sueldo = q('input[name="sueldo_base_mes"]');
  const $grat   = q('input[name="gratificacion"]');
  const $cola   = q('input[name="colacion"]');
  const $trans  = q('input[name="transporte"]');
  const $prev   = q('input[name="cotiz_previsional_obligatoria"]');
  const $salud  = q('input[name="cotiz_salud_obligatoria"]');
  const $ces    = q('input[name="seguro_cesantia"]');
  const $impPS  = q('input[name="imp_prev_salud"]');
  const $otros  = q('input[name="otros_descuentos"]');
  const $th     = q('#total_haberes');
  const $td     = q('#total_descuentos');
  const $liq    = q('input[name="liquido_a_pagar"]');

  /* ── Aplicar formato focus/blur a campos editables ── */
  bindFmt($sueldo);
  bindFmt($cola);
  bindFmt($trans);
  bindFmt($otros);

  /* ── 1. Cargar datos del trabajador al seleccionar ── */
  function cargarTrabajador() {
    const rut = selTrabajador.value;
    const t   = TRABAJADORES_LIQ.find(w => w.rut_trabajador === rut);

    if (!t) {
      [campoNombre, campoRut, campoCargo, campoContrato, campoAfp, campoSaludDisplay].forEach(c => { if(c) c.value = ''; });
      campoSueldo.value = '';
      recalc();
      return;
    }

    campoNombre.value          = t.nombre_completo;
    campoRut.value             = t.rut_trabajador;
    campoCargo.value           = t.nombre_cargo;
    campoContrato.value        = t.nombre_contrato;
    campoAfp.value             = t.nombre_afp;
    if (campoSaludDisplay) campoSaludDisplay.value = t.nombre_salud;

    // Precargar con formato CLP
    campoSueldo.value          = formatCLP(parseFloat(t.sueldo_base_fijo) || 0);
    if ($cola)  $cola.value    = formatCLP(parseFloat(t.colacion)         || 0);
    if ($trans) $trans.value   = formatCLP(parseFloat(t.transporte)       || 0);

    recalc();
  }

  /* ── 2. Seleccionar el último día del mes ── */
  function actualizarDias() {
    const mes  = parseInt(selMes.value,    10);
    const anio = parseInt(campoAnio.value, 10);
    if (!mes || !anio || anio < 2000) return;
    campoDias.value = new Date(anio, mes, 0).getDate();
  }

  /* ── 3. Recalcular todos los totales ── */
  function recalc() {
    const sueldo   = num($sueldo);
    const grat     = Math.round(sueldo * 0.25);
    set($grat, grat);

    const IT       = sueldo + grat;
    const NN       = num($cola) + num($trans);

    const cotPrev  = Math.round(IT * 0.10);
    const cotSalud = Math.round(IT * 0.07);
    const cesantia = Math.round(IT * 0.006);

    set($prev,  cotPrev);
    set($salud, cotSalud);
    set($ces,   cesantia);

    const totalHaberes = IT + NN;
    const DL           = cotPrev + cotSalud + cesantia + num($impPS);
    const DV           = num($otros);
    const totalDesc    = DL + DV;
    const liquidoSug   = totalHaberes - totalDesc;

    set($th, totalHaberes);
    set($td, totalDesc);

    // Líquido: autofill mientras no lo edite el usuario
    if ($liq && ($liq.value === '' || $liq.dataset.autofill === '1')) {
      set($liq, liquidoSug);
      $liq.dataset.autofill = '1';
    }
  }

  /* ── Eventos ── */
  selTrabajador.addEventListener('change', cargarTrabajador);
  selMes.addEventListener('change',        actualizarDias);
  campoAnio.addEventListener('input',      actualizarDias);

  // Recalcular al escribir en campos editables
  [$sueldo, $cola, $trans, $otros].forEach(el => el && el.addEventListener('input', recalc));
  if ($liq) $liq.addEventListener('input', () => { $liq.dataset.autofill = '0'; });

  /* ── Estilo líquido a pagar ── */
  if ($liq) {
    $liq.style.textAlign  = 'center';
    $liq.style.fontSize   = '1.4rem';
    $liq.style.fontWeight = 'bold';
    $liq.readOnly         = true;
  }

  /* ── Inicializar ── */
  if (selTrabajador.value) cargarTrabajador();
  actualizarDias();
  recalc();

})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>