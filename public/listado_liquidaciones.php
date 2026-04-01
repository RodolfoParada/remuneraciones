<?php
// public/listado_liquidaciones.php
require_once __DIR__ . '/../src/Db.php';
$pageTitle = 'Listado de liquidaciones';
include __DIR__ . '/includes/header.php';

$errores = [];
$mensajes = [];
$rows = [];

try {
  $database = new Db();
  $pdo = $database->pdo();

  // --- LÓGICA DE ELIMINACIÓN ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $idEliminar = (int)$_POST['id_liquidacion'];
    
    $stmtDel = $pdo->prepare("DELETE FROM liquidacion WHERE id_liquidacion = ?");
    if ($stmtDel->execute([$idEliminar])) {
        $mensajes[] = "Liquidación #$idEliminar eliminada correctamente.";
    } else {
        $errores[] = "No se pudo eliminar la liquidación.";
    }
  }

  // --- CONSULTA DE LISTADO ---
  $stmt = $pdo->query("
    SELECT 
      l.id_liquidacion,
      l.rut_trabajador,
      t.nombre_completo,
      l.mes_periodo,
      l.anio_periodo,
      l.liquido_a_pagar
    FROM liquidacion l
    JOIN trabajador t ON t.rut_trabajador = l.rut_trabajador
    ORDER BY l.anio_periodo DESC, l.mes_periodo DESC
  ");
  $rows = $stmt->fetchAll();
} catch (Throwable $e) {
  $errores[] = $e->getMessage();
}
?>
<link rel="stylesheet" href="/remuneraciones/public/assets/css/listado_liquidaciones.css" />

<section class="panel">
  <div class="tabs">
    <span class="tab active">Listado Liquidaciones</span>
    <a class="tab" href="/remuneraciones/public/liquidaciones_nueva.php">Crear Nueva liquidación</a>
  </div>

  <?php if (!$rows): ?>
    <p class="center lead">No existen liquidaciones registradas.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>RUT</th>
          <th>Nombre del trabajador</th>
          <th>Mes</th>
          <th>Año</th>
          <th>Líquido a pagar</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id_liquidacion'] ?></td>
            <td><?= htmlspecialchars($r['rut_trabajador']) ?></td>
            <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
            <td><?= (int)$r['mes_periodo'] ?></td>
            <td><?= (int)$r['anio_periodo'] ?></td>
            <td>$<?= number_format((float)$r['liquido_a_pagar'], 0, ',', '.') ?></td>
            <td style="display: flex; gap: 5px;">
              <a class="btn small" href="/remuneraciones/public/liquidacion_ver.php?id=<?= (int)$r['id_liquidacion'] ?>">Ver</a>
              
              <form method="POST" class="form-eliminar">
                <input type="hidden" name="id_liquidacion" value="<?= (int)$r['id_liquidacion'] ?>">
                <input type="hidden" name="action" value="eliminar">
                <button type="button" class="btn small btn-eliminar" 
                        onclick="abrirModalEliminar(this.closest('form'), '<?= htmlspecialchars($r['nombre_completo']) ?>', 'Periodo: <?= $r['mes_periodo'] ?>/<?= $r['anio_periodo'] ?>')">
                Eliminar
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div id="modal-eliminar" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);align-items:center;justify-content:center;backdrop-filter:blur(2px);">
    <div style="background:var(--panel);border:1px solid var(--border,#333);border-radius:14px;padding:28px 32px;max-width:460px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.5); color:var(--txt);">
      <h3 style="margin:0 0 8px;">Confirmar eliminación</h3>
      <p style="margin:0 0 6px;">
        ¿Está seguro que desea eliminar la liquidación de<br>
        <strong id="modal-nombre-trabajador"></strong>?
      </p>
      
      <div id="modal-detalle-trabajador"
           style="margin:10px 0 16px;padding:10px 14px;border-radius:8px;
                  background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
                  font-size:13px;color:var(--muted,#aaa);line-height:1.7">
      </div>
      
      <small style="display:block;margin-bottom:20px;color:#f87171;">Esta acción no se puede deshacer.</small>
      
      <div style="display:flex;gap:12px;justify-content:flex-end">
        <button type="button" onclick="cerrarModalEliminar()" class="btn" style="background:transparent; border:1px solid var(--border); color:var(--txt);">Cancelar</button>
        <button type="button" id="modal-btn-confirmar" class="btn" style="background:#c0392b; color:#fff; border:none;">Sí, eliminar</button>
      </div>
    </div>
  </div>

  <div class="actions">
    <a class="btn ghost btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
  </div>
</section>

<script>
let formPendiente = null;

function abrirModalEliminar(form, nombre, detalle) {
    formPendiente = form;
    document.getElementById('modal-nombre-trabajador').innerText = nombre;
    document.getElementById('modal-detalle-trabajador').innerText = detalle;
    document.getElementById('modal-eliminar').style.display = 'flex';
}

function cerrarModalEliminar() {
    document.getElementById('modal-eliminar').style.display = 'none';
    formPendiente = null;
}

document.getElementById('modal-btn-confirmar').addEventListener('click', function() {
    if (formPendiente) formPendiente.submit();
});

// Cerrar al hacer clic fuera del recuadro
document.getElementById('modal-eliminar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalEliminar();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>