<?php
// public/liquidaciones_nueva.php
require_once __DIR__ . '/../src/Db.php';
$pageTitle = 'Nueva liquidación';

$errores = [];
$datosTrabajador = null;

try {
  $pdo = (new Db())->pdo();

  // Listado para el selector de trabajadores
  $trabajadores = $pdo->query("
    SELECT t.rut_trabajador, t.nombre_completo,
           c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
    FROM trabajador t
    JOIN cargo c ON c.id_cargo = t.id_cargo
    JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
    JOIN afp a ON a.id_afp = t.id_afp
    JOIN sistema_salud s ON s.id_salud = t.id_salud
    ORDER BY t.nombre_completo
  ")->fetchAll();

  // Cargar ficha al seleccionar un trabajador
  $rutSel = $_REQUEST['rut_trabajador'] ?? '';
  if ($rutSel !== '') {
    $stmt = $pdo->prepare("
      SELECT t.rut_trabajador, t.nombre_completo,
             c.nombre_cargo, tc.nombre_contrato, a.nombre_afp, s.nombre_salud
      FROM trabajador t
      JOIN cargo c ON c.id_cargo = t.id_cargo
      JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
      JOIN afp a ON a.id_afp = t.id_afp
      JOIN sistema_salud s ON s.id_salud = t.id_salud
      WHERE t.rut_trabajador = :rut
      LIMIT 1
    ");
    $stmt->execute([':rut' => $rutSel]);
    $datosTrabajador = $stmt->fetch();
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    // -------- Encabezado --------
    $rut   = trim($_POST['rut_trabajador'] ?? '');
    $mes   = (int)($_POST['mes_periodo'] ?? 0);
    $anio  = (int)($_POST['anio_periodo'] ?? 0);
    $dias  = ($_POST['dias_trabajados'] !== '') ? (int)$_POST['dias_trabajados'] : null;

    // -------- HABERES --------
    $valor_uf        = ($_POST['valor_uf'] !== '') ? (float)$_POST['valor_uf'] : null;
    $sueldo_base_mes = ($_POST['sueldo_base_mes'] !== '') ? (float)$_POST['sueldo_base_mes'] : null;
    $gratificacion   = ($_POST['gratificacion'] !== '') ? (float)$_POST['gratificacion'] : null;
    $colacion        = ($_POST['colacion'] !== '') ? (float)$_POST['colacion'] : null;

    // -------- DESCUENTOS (legales auto y voluntarios) --------
    $cotiz_prev      = ($_POST['cotiz_previsional_obligatoria'] !== '') ? (float)$_POST['cotiz_previsional_obligatoria'] : null; // 10% IT (JS)
    $cotiz_salud     = ($_POST['cotiz_salud_obligatoria'] !== '') ? (float)$_POST['cotiz_salud_obligatoria'] : null;             // 7% IT (JS)
    $seguro_cesantia = ($_POST['seguro_cesantia'] !== '') ? (float)$_POST['seguro_cesantia'] : null;                             // 0.60% IT (JS)

    $imp_prev_salud  = ($_POST['imp_prev_salud'] !== '') ? (float)$_POST['imp_prev_salud'] : null; // queda opcional (manual)
    $imp_cesantia    = null; // eliminado del formulario, se guarda como NULL

    $otros_desc      = ($_POST['otros_descuentos'] !== '') ? (float)$_POST['otros_descuentos'] : null;

    // -------- RESUMEN --------
    $base_tributable = ($_POST['base_tributable'] !== '') ? (float)$_POST['base_tributable'] : null;
    $liquido         = ($_POST['liquido_a_pagar'] !== '') ? (float)$_POST['liquido_a_pagar'] : null;

    // Validaciones mínimas
    if ($rut === '')             $errores[] = 'Seleccione un trabajador.';
    if ($mes < 1 || $mes > 12)   $errores[] = 'Mes inválido.';
    if ($anio < 2000)            $errores[] = 'Año inválido.';
    if ($liquido === null)       $errores[] = 'Debe indicar el líquido a pagar.';

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
           :ces, :otros, :impPrev, :impCes,
           :base, :liq)
      ");
      $stmt->execute([
        ':rut'       => $rut,
        ':mes'       => $mes,
        ':anio'      => $anio,
        ':empleador' => 'Colegio Ejemplo',
        ':dias'      => $dias,
        ':uf'        => $valor_uf,
        ':sueldo'    => $sueldo_base_mes,
        ':grat'      => $gratificacion,
        ':col'       => $colacion,
        ':prev'      => $cotiz_prev,
        ':sal'       => $cotiz_salud,
        ':ces'       => $seguro_cesantia,
        ':otros'     => $otros_desc,
        ':impPrev'   => $imp_prev_salud,
        ':impCes'    => $imp_cesantia, // NULL
        ':base'      => $base_tributable,
        ':liq'       => $liquido,
      ]);

      $lastId = (int)$pdo->lastInsertId();
      header('Location: /liquidacion_ver.php?id=' . $lastId);
      exit;
    }
  }
} catch (Throwable $e) {
  $errores[] = 'Error: ' . $e->getMessage();
}

include __DIR__ . '/includes/header.php';
?>

<section class="panel">
  <div class="tabs">
    <a class="tab" href="/listado_liquidaciones.php">Listado</a>
    <span class="tab active">Nueva</span>
  </div>

  <?php if ($errores): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f">
      <div class="badge">Se encontraron errores</div>
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form" action="/liquidaciones_nueva.php">
    <!-- ===== ENCABEZADO ===== -->
    <fieldset>
      <legend>Identificación de la liquidación</legend>

      <label>Trabajador
        <select name="rut_trabajador" required onchange="this.form.submit()">
          <option value="">-- Seleccione --</option>
          <?php foreach ($trabajadores as $t): ?>
            <option value="<?= htmlspecialchars($t['rut_trabajador']) ?>"
              <?= ($t['rut_trabajador'] === $rutSel ? 'selected' : '') ?>>
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
            <input type="text" value="<?= $datosTrabajador['nombre_contrato'] ?? '' ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Nombre completo
            <input type="text" value="<?= $datosTrabajador['nombre_completo'] ?? '' ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>RUT
            <input type="text" value="<?= $datosTrabajador['rut_trabajador'] ?? '' ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Cargo
            <input type="text" value="<?= $datosTrabajador['nombre_cargo'] ?? '' ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Días trabajados
            <input type="number" name="dias_trabajados" min="0" max="31" value="<?= htmlspecialchars($_POST['dias_trabajados'] ?? '') ?>">
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>AFP
            <input type="text" value="<?= $datosTrabajador['nombre_afp'] ?? '' ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Salud
            <input type="text" value="<?= $datosTrabajador['nombre_salud'] ?? '' ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Mes
            <select name="mes_periodo" required>
              <option value="">--</option>
              <?php for ($m=1; $m<=12; $m++): ?>
                <option value="<?= $m ?>" <?= (isset($_POST['mes_periodo']) && (int)$_POST['mes_periodo']===$m ? 'selected':'') ?>><?= $m ?></option>
              <?php endfor; ?>
            </select>
          </label>
        </div>
        <div class="flex-1">
          <label>Año
            <input type="number" name="anio_periodo" min="2000" value="<?= htmlspecialchars($_POST['anio_periodo'] ?? date('Y')) ?>" required>
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
                <td><input type="number" step="0.01" name="sueldo_base_mes" value="<?= htmlspecialchars($_POST['sueldo_base_mes'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Gratificación</td>
                <td><input type="number" step="0.01" name="gratificacion" value="<?= htmlspecialchars($_POST['gratificacion'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Colación / Movilización (no imponible)</td>
                <td><input type="number" step="0.01" name="colacion" value="<?= htmlspecialchars($_POST['colacion'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Valor UF</td>
                <td><input type="number" step="0.01" name="valor_uf" value="<?= htmlspecialchars($_POST['valor_uf'] ?? '') ?>"></td>
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
                <td>Impuesto previsión </td>
                <td><input type="number" step="0.01" name="imp_prev_salud" value="<?= htmlspecialchars($_POST['imp_prev_salud'] ?? '') ?>"></td>
              </tr>
              <tr>
                <td>Otros descuentos (voluntarios)</td>
                <td><input type="number" step="0.01" name="otros_descuentos" value="<?= htmlspecialchars($_POST['otros_descuentos'] ?? '') ?>"></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
    </div>

    <!-- ===== TOTALES DETALLADOS ===== -->
    <fieldset>
      <legend>Totales por tipo</legend>
      <div class="flex">
        <div class="flex-1">
          <table class="table">
            <tbody>
              <tr>
                <td><strong>(IT)</strong> Imponible tributable</td>
                <td><input type="number" step="0.01" id="it_imponible" name="it_imponible" readonly></td>
              </tr>
              <tr>
                <td><strong>(NN)</strong> No imponible y no tributable</td>
                <td><input type="number" step="0.01" id="nn_no_imponible" name="nn_no_imponible" readonly></td>
              </tr>
              <tr>
                <td><strong>TOTAL HABERES</strong></td>
                <td><input type="number" step="0.01" id="total_haberes" name="total_haberes" readonly></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex-1">
          <table class="table">
            <tbody>
              <tr>
                <td><strong>(DL)</strong> Descuentos legales</td>
                <td><input type="number" step="0.01" id="dl_legales" name="dl_legales" readonly></td>
              </tr>
              <tr>
                <td><strong>(DV)</strong> Descuentos voluntarios</td>
                <td><input type="number" step="0.01" id="dv_voluntarios" name="dv_voluntarios" readonly></td>
              </tr>
              <tr>
                <td><strong>TOTAL DESCUENTOS</strong></td>
                <td><input type="number" step="0.01" id="total_descuentos" name="total_descuentos" readonly></td>
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
          <label>💰 Líquido a pagar (obligatorio)
            <input type="number" step="0.01" name="liquido_a_pagar" required value="<?= htmlspecialchars($_POST['liquido_a_pagar'] ?? '') ?>">
          </label>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn primary" name="guardar" type="submit">Guardar liquidación</button>
      <a class="btn ghost" href="/remuneraciones/public/listado_liquidaciones.php">Volver</a>
    </div>

    <!-- ===== CÁLCULO AUTOMÁTICO (JS) ===== -->
    <script>
    (function(){
      const q = s => document.querySelector(s);

      const num = el => {
        if (!el) return 0;
        const v = parseFloat((el.value || '').toString().replace(',', '.'));
        return isNaN(v) ? 0 : v;
      };
      const set = (el, val) => { if (el) el.value = (Math.round((val+Number.EPSILON)*100)/100).toFixed(2); };

      // HABERES
      const $sueldo = q('input[name="sueldo_base_mes"]');
      const $grat   = q('input[name="gratificacion"]');
      const $cola   = q('input[name="colacion"]');

      // DESCUENTOS (se calculan de IT)
      const $prev   = q('input[name="cotiz_previsional_obligatoria"]'); // 10% IT
      const $salud  = q('input[name="cotiz_salud_obligatoria"]');       // 7% IT
      const $ces    = q('input[name="seguro_cesantia"]');               // 0.60% IT
      const $impPS  = q('input[name="imp_prev_salud"]');                // opcional
      const $otros  = q('input[name="otros_descuentos"]');

      // Totales visibles
      const $it  = q('#it_imponible');
      const $nn  = q('#nn_no_imponible');
      const $th  = q('#total_haberes');
      const $dl  = q('#dl_legales');
      const $dv  = q('#dv_voluntarios');
      const $td  = q('#total_descuentos');

      // Resumen
      const $liq  = q('input[name="liquido_a_pagar"]');

      function recalc() {
        // Imponible tributable = sueldo + gratificación
        const IT = num($sueldo) + num($grat);
        // No imponible/no tributable
        const NN = num($cola);

        // Cálculo de descuentos legales por % de IT
        const cotPrev  = IT * 0.10;  // 10%
        const cotSalud = IT * 0.07;  // 7%
        const cesantia = IT * 0.006; // 0.60%

        set($prev,  cotPrev);
        set($salud, cotSalud);
        set($ces,   cesantia);

        const totalHaberes = IT + NN;

        // DL = previsión + salud + cesantía + impuesto prev
        const DL = cotPrev + cotSalud + cesantia + num($impPS);
        // DV = otros descuentos
        const DV = num($otros);

        const totalDesc = DL + DV;
        const liquidoSug = totalHaberes - totalDesc;
        const baseSug = IT;

        set($it, IT); set($nn, NN); set($th, totalHaberes);
        set($dl, DL); set($dv, DV); set($td, totalDesc);

        if ($base && ($base.value === '' || $base.dataset.autofill === '1')) {
          $base.value = baseSug.toFixed(2); $base.dataset.autofill = '1';
        }
        if ($liq && ($liq.value === '' || $liq.dataset.autofill === '1')) {
          $liq.value = liquidoSug.toFixed(2); $liq.dataset.autofill = '1';
        }
      }

      const wire = el => el && el.addEventListener('input', recalc);
      [$sueldo,$grat,$cola,$impPS,$otros].forEach(wire);

      if ($base) $base.addEventListener('input', ()=> $base.dataset.autofill='0');
      if ($liq)  $liq.addEventListener('input',  ()=> $liq .dataset.autofill='0');

      recalc();
    })();
    </script>
  </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>