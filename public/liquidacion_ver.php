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

    $IT           = (float)($row['sueldo_base_mes'] ?? 0) + (float)($row['gratificacion'] ?? 0);
    $NN           = (float)($row['colacion'] ?? 0);
    $totalHaberes = $IT + $NN;
    $DL           = (float)($row['cotiz_previsional_obligatoria'] ?? 0)
                  + (float)($row['cotiz_salud_obligatoria']       ?? 0)
                  + (float)($row['seguro_cesantia']               ?? 0)
                  + (float)($row['imp_prev_salud']                ?? 0);
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
    <span class="tab active">Detalle de liquidación</span>
    <a class="tab" href="/remuneraciones/public/listado_liquidaciones.php">Listado Liquidaciones</a>
  </div>

  <?php if ($errores): ?>
    <div class="panel-form-error">
      <span class="badge-error">Error</span>
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php else: ?>

    <div class="actions" style="margin-bottom:16px">
      <a class="btn" href="/remuneraciones/public/listado_liquidaciones.php">Volver</a>
      <button class="btn primary" onclick="window.print()">Imprimir / PDF</button>
    </div>

    <form class="form">

      <!-- ===== IDENTIFICACIÓN ===== -->
      <fieldset>
        <legend>Identificación de la liquidación</legend>

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
              <input type="text" value="<?= (int)$row['mes_periodo'] ?>" disabled>
            </label>
          </div>
          <div class="flex-1">
            <label>Año
              <input type="text" value="<?= (int)$row['anio_periodo'] ?>" disabled>
            </label>
          </div>
        </div>
      </fieldset>

      <!-- ===== HABERES y DESCUENTOS ===== -->
      <div class="flex">
        <div class="flex-1">
          <fieldset>
            <legend>Haberes</legend>
            <table class="table">
              <tbody>
                <tr><td>Sueldo base mes</td>        <td>$<?= number_format((float)$row['sueldo_base_mes'], 0, ',', '.') ?></td></tr>
                <tr><td>Gratificación</td>           <td>$<?= number_format((float)$row['gratificacion'],   0, ',', '.') ?></td></tr>
                <tr><td>Colación / Movilización</td> <td>$<?= number_format((float)$row['colacion'],        0, ',', '.') ?></td></tr>
                <tr><td>Valor UF</td>                <td><?= $row['valor_uf'] !== null ? number_format((float)$row['valor_uf'], 2, ',', '.') : '—' ?></td></tr>
              </tbody>
            </table>
          </fieldset>
        </div>
        <div class="flex-1">
          <fieldset>
            <legend>Descuentos</legend>
            <table class="table">
              <tbody>
                <tr><td>Cotiz. previsional (10% IT)</td><td>$<?= number_format((float)$row['cotiz_previsional_obligatoria'], 0, ',', '.') ?></td></tr>
                <tr><td>Cotiz. salud (7% IT)</td>        <td>$<?= number_format((float)$row['cotiz_salud_obligatoria'],       0, ',', '.') ?></td></tr>
                <tr><td>Seguro cesantía (0,60% IT)</td>  <td>$<?= number_format((float)$row['seguro_cesantia'],               0, ',', '.') ?></td></tr>
                <tr><td>Impuesto previsión</td>           <td>$<?= number_format((float)$row['imp_prev_salud'],                0, ',', '.') ?></td></tr>
                <tr><td>Otros descuentos</td>             <td>$<?= number_format((float)$row['otros_descuentos'],              0, ',', '.') ?></td></tr>
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
                <tr><td><strong>(IT)</strong> Imponible tributable</td>         <td>$<?= number_format($IT,           0, ',', '.') ?></td></tr>
                <tr><td><strong>(NN)</strong> No imponible y no tributable</td>  <td>$<?= number_format($NN,           0, ',', '.') ?></td></tr>
                <tr><td><strong>TOTAL HABERES</strong></td>                      <td>$<?= number_format($totalHaberes, 0, ',', '.') ?></td></tr>
              </tbody>
            </table>
          </div>
          <div class="flex-1">
            <table class="table">
              <tbody>
                <tr><td><strong>(DL)</strong> Descuentos legales</td>    <td>$<?= number_format($DL,        0, ',', '.') ?></td></tr>
                <tr><td><strong>(DV)</strong> Descuentos voluntarios</td> <td>$<?= number_format($DV,        0, ',', '.') ?></td></tr>
                <tr><td><strong>TOTAL DESCUENTOS</strong></td>            <td>$<?= number_format($totalDesc, 0, ',', '.') ?></td></tr>
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
            <label>Líquido a pagar
              <input type="text" value="$<?= number_format($liquidoCalc, 0, ',', '.') ?>" disabled>
            </label>
          </div>
        </div>
      </fieldset>

      <div class="actions">
        <a class="btn" href="/remuneraciones/public/listado_liquidaciones.php">Volver</a>
        <button class="btn primary" onclick="window.print()">Imprimir / PDF</button>
      </div>

    </form>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>