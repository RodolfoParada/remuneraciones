<?php
// public/liquidacion_ver.php
require_once __DIR__ . '/../src/Db.php';

$pageTitle = 'Detalle de liquidación';
$errores   = [];
$row       = null;

try {
    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        throw new RuntimeException('Identificador inválido.');
    }
    $id  = (int)$_GET['id'];
    $pdo = (new Db())->pdo();

    $stmt = $pdo->prepare("
        SELECT l.*,
               l.cotiz_previsional AS cotiz_previsional_obligatoria,
               l.cotiz_salud       AS cotiz_salud_obligatoria,
               t.nombre_completo, t.rut_trabajador,
               c.nombre_cargo, tc.nombre_contrato,
               a.nombre_afp, s.nombre_salud
        FROM liquidacion l
        JOIN trabajador t      ON t.rut_trabajador    = l.rut_trabajador
        JOIN cargo c           ON c.id_cargo          = t.id_cargo
        JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
        JOIN afp a             ON a.id_afp            = t.id_afp
        JOIN sistema_salud s   ON s.id_salud          = t.id_salud
        WHERE l.id_liquidacion = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) throw new RuntimeException('Liquidación no encontrada.');

    $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
              'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    $IT           = (float)($row['sueldo_base_mes'] ?? 0) + (float)($row['gratificacion'] ?? 0);
    $NN           = (float)($row['colacion']        ?? 0) + (float)($row['transporte']    ?? 0);
    $totalHaberes = $IT + $NN;

    $impUnico     = (float)($row['impuesto_unico']  ?? 0);

    $DL = (float)($row['cotiz_previsional'] ?? 0)
        + (float)($row['cotiz_salud']       ?? 0)
        + (float)($row['seguro_cesantia']   ?? 0)
        + $impUnico;
    $DV           = (float)($row['otros_descuentos'] ?? 0);
    $totalDesc    = $DL + $DV;
    $liquidoCalc  = $totalHaberes - $totalDesc;

} catch (Throwable $e) {
    $errores[] = $e->getMessage();
}

include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/formularios.css" />

<style>
@media print {
  header.topbar, .actions, .tabs, footer { display: none !important; }
  body  { background: #fff !important; color: #000 !important; }
  .panel, .table { border-color: #000 !important; box-shadow: none !important; }
  .table th, .table td { border-bottom: 1px solid #000 !important; }
}
</style>

<section class="panel">
  <div class="tabs">
    <a class="tab" href="/remuneraciones/public/listado_liquidaciones.php">Listado Liquidaciones</a>
    <span class="tab active">Detalle de Liquidación</span>
  </div>

  <?php if ($errores): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f;color:#fff;margin-bottom:12px">
      <div class="badge" style="background:#7f1d1d;border-color:#991b1b;color:#fff">Errores</div>
      <ul style="color:#fff"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php else: ?>

  <form class="form">

    <!-- ENCABEZADO -->
    <fieldset>
      <legend>Identificación de la liquidación</legend>

      <label>Trabajador
        <input type="text" value="<?= htmlspecialchars($row['nombre_completo']) ?> (<?= htmlspecialchars($row['rut_trabajador']) ?>)" disabled>
      </label>

      <div class="flex">
        <div class="flex-1">
          <label>Empresa
            <input type="text" value="<?= htmlspecialchars($row['nombre_empleador'] ?? 'Colegio Ejemplo') ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Tipo de contrato
            <input type="text" value="<?= htmlspecialchars($row['nombre_contrato']) ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Nombre completo
            <input type="text" value="<?= htmlspecialchars($row['nombre_completo']) ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>RUT
            <input type="text" value="<?= htmlspecialchars($row['rut_trabajador']) ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Cargo
            <input type="text" value="<?= htmlspecialchars($row['nombre_cargo']) ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Días trabajados
            <input type="text" value="<?= htmlspecialchars($row['dias_trabajados']) ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>AFP
            <input type="text" value="<?= htmlspecialchars($row['nombre_afp']) ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Salud
            <input type="text" value="<?= htmlspecialchars($row['nombre_salud']) ?>" disabled>
          </label>
        </div>
      </div>

      <div class="flex">
        <div class="flex-1">
          <label>Mes
            <input type="text" value="<?= (int)$row['mes_periodo'] ?> — <?= htmlspecialchars($meses[(int)$row['mes_periodo']] ?? '') ?>" disabled>
          </label>
        </div>
        <div class="flex-1">
          <label>Año
            <input type="text" value="<?= (int)$row['anio_periodo'] ?>" disabled>
          </label>
        </div>
      </div>
    </fieldset>

    <!-- HABERES Y DESCUENTOS -->
    <div class="flex">
      <div class="flex-1">
        <fieldset>
          <table class="table">
            <tbody>
              <tr class="titulos-fijos">
                <td colspan="2" style="text-align:center;font-weight:bold;">HABERES IMPONIBLES</td>
              </tr>
              <tr>
                <td>Sueldo base</td>
                <td><input type="text" value="$<?= number_format((float)$row['sueldo_base_mes'], 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr>
                <td>Gratificación (25% c/tope)</td>
                <td><input type="text" value="$<?= number_format((float)$row['gratificacion'], 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr class="titulos-fijos">
                <td colspan="2" style="text-align:center;font-weight:bold;">HABERES NO IMPONIBLES</td>
              </tr>
              <tr>
                <td>Colación</td>
                <td><input type="text" value="$<?= number_format((float)$row['colacion'], 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr>
                <td>Movilización</td>
                <td><input type="text" value="$<?= number_format((float)($row['transporte'] ?? 0), 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr class="total-fijos">
                <td><strong>TOTAL HABERES</strong></td>
                <td><input type="text" value="$<?= number_format($totalHaberes, 0, ',', '.') ?>" disabled></td>
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
                <td colspan="2" style="text-align:center;font-weight:bold;">DESCUENTOS LEGALES</td>
              </tr>
              <tr>
                <td>Cotiz. previsional obligatoria (AFP) <span class="badge">10% IT</span></td>
                <td><input type="text" value="$<?= number_format((float)$row['cotiz_previsional_obligatoria'], 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr>
                <td>Cotiz. salud obligatoria <span class="badge">7% IT</span></td>
                <td><input type="text" value="$<?= number_format((float)$row['cotiz_salud_obligatoria'], 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr>
                <td>Seguro cesantía <span class="badge">0,60% IT</span></td>
                <td><input type="text" value="$<?= number_format((float)$row['seguro_cesantia'], 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr>
                <td>
                  Impuesto Único 2ª Categoría
                  <?php if ($impUnico > 0): ?>
                    <span class="badge" style="background:rgba(245,158,11,.12);color:#fbbf24;border-color:rgba(245,158,11,.25);">Aplicado</span>
                  <?php else: ?>
                    <span class="badge" style="background:rgba(16,185,129,.12);color:#4ade80;border-color:rgba(16,185,129,.25);">Exento</span>
                  <?php endif; ?>
                </td>
                <td><input type="text" value="$<?= number_format($impUnico, 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr class="titulos-fijos">
                <td colspan="2" style="text-align:center;font-weight:bold;">OTROS DESCUENTOS</td>
              </tr>
              <tr>
                <td>Otros descuentos</td>
                <td><input type="text" value="$<?= number_format((float)($row['otros_descuentos'] ?? 0), 0, ',', '.') ?>" disabled></td>
              </tr>
              <tr class="total-fijos">
                <td><strong>TOTAL DESCUENTOS</strong></td>
                <td><input type="text" value="$<?= number_format($totalDesc, 0, ',', '.') ?>" disabled></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
    </div>

    <!-- RESUMEN -->
    <fieldset>
      <div class="flex">
        <div class="flex-1">
          <label>LIQUIDO A RECIBIR
            <input type="text" value="$<?= number_format($liquidoCalc, 0, ',', '.') ?>" disabled
                   style="text-align:center;font-size:1.4rem;font-weight:bold;">
          </label>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn azul" onclick="window.print()">Imprimir / PDF</button>
      <a class="btn ghost btn-volver" href="/remuneraciones/public/listado_liquidaciones.php">Volver al Listado</a>
    </div>

    <h4>Certifico que he recibido de del colegio a mi entera satisfacción el saldo indicado en la presente Liquidación y no tengo cargo ni cobro posterior que hacer</h4>
    <br><br><br>
    ________________________
    <h3>FIRMA CONFORME</h3>

  </form>

  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>