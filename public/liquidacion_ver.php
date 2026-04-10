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
        JOIN trabajador     t  ON t.rut_trabajador    = l.rut_trabajador
        JOIN cargo          c  ON c.id_cargo          = t.id_cargo
        JOIN tipos_contrato tc ON tc.id_tipo_contrato = t.id_tipo_contrato
        JOIN afp            a  ON a.id_afp            = t.id_afp
        JOIN sistema_salud  s  ON s.id_salud          = t.id_salud
        WHERE l.id_liquidacion = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) throw new RuntimeException('Liquidación no encontrada.');

    $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
              'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    $horasMonto   = (float)($row['horas_extra_monto']    ?? 0);
    $horasQty     = (int)  ($row['horas_extra_cantidad'] ?? 0);
    $IT           = (float)($row['sueldo_base_mes'] ?? 0)
                  + (float)($row['gratificacion']   ?? 0)
                  + $horasMonto;
    $NN           = (float)($row['colacion']   ?? 0)
                  + (float)($row['transporte'] ?? 0);
    $totalHaberes = $IT + $NN;
    $impUnico     = (float)($row['impuesto_unico'] ?? 0);
    $DL           = (float)($row['cotiz_previsional'] ?? 0)
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

<link rel="stylesheet" href="<?= $basePath ?>assets/css/formularios.css" />

<style>
/* ══════════════════════════════════════════
   IMPRESIÓN — replica exacta del formulario
   en pantalla, una sola página A4
══════════════════════════════════════════ */
@media print {

    /* ── Ocultar lo que no debe imprimirse ── */
    header.topbar,
    nav,
    .tabs,
    .actions,
    footer,
    .pie-pagina {
        display: none !important;
    }

    /* ── Página A4 márgenes ajustados ── */
    @page {
        size: A4 portrait;
        margin: 10mm 12mm 10mm 12mm;
    }

    /* ── Base ── */
    *, *::before, *::after {
        box-sizing: border-box !important;
    }

    html, body {
        width:      210mm !important;
        background: #fff !important;
        color:      #000 !important;
        font-size:  10px !important;
        font-family: system-ui, Arial, sans-serif !important;
        margin:  0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ── Sin saltos de página internos ── */
    * {
        page-break-inside: avoid !important;
        break-inside:      avoid !important;
    }

    /* ── Panel ── */
    section.panel {
        margin:     0 !important;
        padding:    0 !important;
        border:     none !important;
        box-shadow: none !important;
        width:      100% !important;
    }

    /* ── Fieldsets ── */
    fieldset {
        border:        1px solid #d1d5db !important;
        border-radius: 8px !important;
        padding:       6px 10px !important;
        margin-bottom: 6px !important;
        background:    #fff !important;
    }

    legend {
        font-size:   9px !important;
        font-weight: 700 !important;
        padding:     0 6px !important;
        color:       #374151 !important;
        text-transform: uppercase !important;
        letter-spacing: .03em !important;
    }

    /* ── Flex — mantiene las dos columnas ── */
    .flex {
        display:       flex !important;
        flex-direction: row !important;
        gap:           8px !important;
        margin-bottom: 4px !important;
        width:         100% !important;
    }

    .flex-1 {
        flex: 1 1 0 !important;
        min-width: 0 !important;
    }

    /* ── Labels ── */
    label {
        display:        flex !important;
        flex-direction: column !important;
        font-size:      8px !important;
        font-weight:    600 !important;
        color:          #6b7280 !important;
        gap:            2px !important;
        margin-bottom:  3px !important;
        text-transform: uppercase !important;
        letter-spacing: .03em !important;
    }

    /* ── Inputs ── */
    input[disabled],
    input:disabled {
        display:        block !important;
        width:          100% !important;
        border:         1px solid #d1d5db !important;
        border-radius:  6px !important;
        padding:        3px 8px !important;
        font-size:      9px !important;
        background:     #f9fafb !important;
        color:          #111827 !important;
        box-shadow:     none !important;
        height:         22px !important;
        line-height:    22px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ── Input líquido a pagar ── */
    input[style*="1.4rem"] {
        font-size:   14px !important;
        font-weight: 800 !important;
        text-align:  center !important;
        height:      32px !important;
        line-height: 32px !important;
        color:       #111827 !important;
    }

    /* ── Tablas haberes/descuentos ── */
    .table {
        width:           100% !important;
        border-collapse: separate !important;
        border-spacing:  0 !important;
        border:          1px solid #d1d5db !important;
        border-radius:   8px !important;
        overflow:        hidden !important;
        font-size:       9px !important;
        margin:          0 !important;
    }

    .table td {
        padding:        4px 8px !important;
        border-bottom:  1px solid #e5e7eb !important;
        font-size:      9px !important;
        color:          #111827 !important;
        vertical-align: middle !important;
    }

    .table tr:last-child td {
        border-bottom: none !important;
    }

    /* Input dentro de celda tabla */
    .table td input[disabled] {
        border:     1px solid #d1d5db !important;
        padding:    2px 6px !important;
        font-size:  9px !important;
        width:      100% !important;
        text-align: right !important;
        height:     20px !important;
        line-height: 20px !important;
        background: #f9fafb !important;
        color:      #111827 !important;
        border-radius: 4px !important;
    }

    /* ── Titulos fijos — gris medio ── */
    .titulos-fijos td {
        background:  #1f2937 !important;
        color:       #fff !important;
        font-weight: bold !important;
        font-size:   8.5px !important;
        text-align:  center !important;
        padding:     3px 8px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ── Total fijos — gris oscuro ── */
    .total-fijos td {
        background:  #6b7280 !important;
        color:       #fff !important;
        font-weight: bold !important;
        font-size:   9px !important;
        padding:     3px 8px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .total-fijos td input[disabled] {
        background:  #6b7280 !important;
        color:       #fff !important;
        border:      none !important;
        font-weight: bold !important;
        text-align:  right !important;
    }

    /* ── Badges ── */
    .badge {
        font-size:     7px !important;
        padding:       1px 4px !important;
        border:        1px solid #d1d5db !important;
        border-radius: 999px !important;
        background:    #f3f4f6 !important;
        color:         #374151 !important;
        display:       inline-block !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ── Small ── */
    small {
        font-size: 7px !important;
        color:     #9ca3af !important;
    }

    /* ── Firma ── */
    h4 {
        font-size:  8.5px !important;
        margin:     8px 0 2px !important;
        color:      #374151 !important;
        font-weight: normal !important;
    }

    h3 {
        font-size:  9px !important;
        margin:     4px 0 0 !important;
        color:      #111827 !important;
    }

    /* ── Eliminar br vacíos ── */
    br {
        display: none !important;
    }
}
</style>

<section class="panel">

    <div class="tabs">
        <a class="tab" href="<?= $basePath ?>listado_liquidaciones.php">Listado Liquidaciones</a>
        <span class="tab active">Detalle de Liquidación</span>
    </div>

    <?php if ($errores): ?>
        <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f;color:#fff;margin-bottom:12px">
            <div class="badge" style="background:#7f1d1d;border-color:#991b1b;color:#fff">Errores</div>
            <ul style="color:#fff">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>

    <form class="form">

        <!-- ══ IDENTIFICACIÓN ══ -->
        <fieldset>
            <legend>Identificación de la liquidación</legend>

            <label>Trabajador
                <input type="text"
                       value="<?= htmlspecialchars($row['nombre_completo']) ?> (<?= htmlspecialchars($row['rut_trabajador']) ?>)"
                       disabled>
            </label>

            <div class="flex">
                <div class="flex-1">
                    <label>Empresa
                        <input type="text" value="<?= htmlspecialchars($row['nombre_empleador'] ?? 'Liceo N°14 Juan Gomez Millas') ?>" disabled>
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
                        <input type="text"
                               value="<?= (int)$row['mes_periodo'] ?> — <?= htmlspecialchars($meses[(int)$row['mes_periodo']] ?? '') ?>"
                               disabled>
                    </label>
                </div>
                <div class="flex-1">
                    <label>Año
                        <input type="text" value="<?= (int)$row['anio_periodo'] ?>" disabled>
                    </label>
                </div>
            </div>
        </fieldset>

        <!-- ══ HABERES Y DESCUENTOS — dos columnas ══ -->
        <div class="flex">

            <!-- Haberes -->
            <div class="flex-1">
                <fieldset>
                    <table class="table">
                        <tbody>
                            <tr class="titulos-fijos">
                                <td colspan="2">HABERES IMPONIBLES</td>
                            </tr>
                            <tr>
                                <td>Sueldo base</td>
                                <td><input type="text" value="$<?= number_format((float)$row['sueldo_base_mes'], 0, ',', '.') ?>" disabled></td>
                            </tr>
                            <tr>
                                <td>Gratificación (25% c/tope)</td>
                                <td><input type="text" value="$<?= number_format((float)$row['gratificacion'], 0, ',', '.') ?>" disabled></td>
                            </tr>
                            <tr>
                                <td>
                                    Horas extras <span class="badge"><?= $horasQty ?> hrs</span>
                                    <small style="display:block;font-size:11px;color:var(--muted)">Valor hora × 1,5 (recargo 50%)</small>
                                </td>
                                <td><input type="text" value="$<?= number_format($horasMonto, 0, ',', '.') ?>" disabled></td>
                            </tr>
                            <tr class="titulos-fijos">
                                <td colspan="2">HABERES NO IMPONIBLES</td>
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

            <!-- Descuentos -->
            <div class="flex-1">
                <fieldset>
                    <table class="table">
                        <tbody>
                            <tr class="titulos-fijos">
                                <td colspan="2">DESCUENTOS LEGALES</td>
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
                                        <span class="badge" style="background:rgba(245,158,11,.12);color:#d97706;border-color:rgba(245,158,11,.4);">Aplicado</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:rgba(16,185,129,.12);color:#059669;border-color:rgba(16,185,129,.4);">Exento</span>
                                    <?php endif; ?>
                                </td>
                                <td><input type="text" value="$<?= number_format($impUnico, 0, ',', '.') ?>" disabled></td>
                            </tr>
                            <tr class="titulos-fijos">
                                <td colspan="2">OTROS DESCUENTOS</td>
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

        </div><!-- /flex haberes/descuentos -->

        <!-- ══ LÍQUIDO A PAGAR ══ -->
        <fieldset>
            <div class="flex">
                <div class="flex-1">
                    <label>LIQUIDO A RECIBIR
                        <input type="text"
                               value="$<?= number_format($liquidoCalc, 0, ',', '.') ?>"
                               disabled
                               style="text-align:center;font-size:1.4rem;font-weight:bold;">
                    </label>
                </div>
            </div>
        </fieldset>

        <!-- ══ BOTONES (no se imprimen) ══ -->
        <div class="actions">
            <button class="btn azul" onclick="window.print()">Imprimir / PDF</button>
            <a class="btn ghost-confirmar btn-volver"
               href="<?= $basePath ?>listado_liquidaciones.php">Volver al Listado</a>
        </div>

        <!-- ══ FIRMA ══ -->
        <h4>Certifico que he recibido del colegio a mi entera satisfacción el saldo indicado
            en la presente Liquidación y no tengo cargo ni cobro posterior que hacer</h4>
        <br><br><br>________________________
        <h3>FIRMA CONFORME</h3>

    </form>

    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>