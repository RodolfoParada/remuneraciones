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

<?php if (!empty($mensajes)): ?>
  <div id="alert-message" class="panel alert-success-custom" style="margin-bottom: 1.5rem; position: relative;">
    <div class="badge success">Éxito</div>
    <ul style="list-style: none; margin: 0; padding: 0.8rem 0 0 0;">
      <?php foreach ($mensajes as $m): ?>
        <li style="font-weight: 600; color: var(--txt);"><?= htmlspecialchars($m) ?></li>
      <?php endforeach; ?>
    </ul>
    <span onclick="this.parentElement.remove()" style="position: absolute; top: 10px; right: 15px; cursor: pointer; opacity: 0.5;">&times;</span>
  </div>
<?php endif; ?>


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
                <button type="button" class="btn small btn-eliminar" onclick="abrirModal(this.closest('form'))">
                Eliminar
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div id="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center;">
   <div style="background:var(--color-background-primary); border:0.5px solid var(--color-border-tertiary); border-radius:var(--border-radius-lg); padding:2rem; max-width:420px; width:90%; text-align:center;">
     
     <div style="width:48px; height:48px; border-radius:50%; background:var(--color-background-danger); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
       <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--color-text-danger)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
         <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
       </svg>
     </div>
 
     <p style="font-size:17px; font-weight:500; color:var(--color-text-primary); margin:0 0 0.4rem;">¿Eliminar esta liquidación?</p>
     <p style="font-size:14px; color:var(--color-text-secondary); margin:0 0 1.5rem;">Esta acción es permanente y no se puede deshacer.</p>
 
     <div style="display:flex; gap:10px; justify-content:center;">
       <button onclick="cerrarModal()" style="padding:9px 22px; border-radius:var(--border-radius-md); border:0.5px solid var(--color-border-secondary); background:transparent; color:var(--color-text-primary); font-size:14px; cursor:pointer;">
         Cancelar
       </button>
       <button id="btn-confirmar-eliminar" style="padding:9px 22px; border-radius:var(--border-radius-md); border:none; background:#c0392b; color:#fff; font-size:14px; font-weight:500; cursor:pointer;">
         Sí, eliminar
       </button>
     </div>
   </div>
 </div>
  <div class="actions">
   <a class="btn ghost  btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
  </div>
</section>



<script>
let formPendiente = null;

  function abrirModal(form) {
    formPendiente = form;
    document.getElementById('modal-overlay').style.display = 'flex';
  }

  function cerrarModal() {
    document.getElementById('modal-overlay').style.display = 'none';
  }

  document.getElementById('btn-confirmar-eliminar').addEventListener('click', function() {
    if (formPendiente) formPendiente.submit();
  });

  // Cerrar al hacer clic fuera
  document.getElementById('modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
  });

  // Auto-dismiss corregido
  document.addEventListener('DOMContentLoaded', function() {
    const alert = document.getElementById('alert-message');
    if (alert) {
      setTimeout(() => {
        alert.classList.add('fade-out');
        setTimeout(() => alert.remove(), 500);
      }, 4000);
    }
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>