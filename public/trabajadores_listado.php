<?php
require_once __DIR__ . '/../src/Db.php';


$pageTitle = 'Listado de trabajadores';
$okMsg     = '';
$errMsg    = '';

try {
    $pdo = (new Db())->pdo();

    $rows = $pdo->query("
     SELECT t.rut_trabajador, t.nombre_completo, c.nombre_cargo,
               tc.nombre_contrato, a.nombre_afp, s.nombre_salud,
               t.sueldo_base_fijo, 
               COALESCE(t.colacion, 0) AS colacion, 
               COALESCE(t.transporte, 0) AS transporte,
               t.fecha_inicio_contrato, t.fecha_termino_contrato
        FROM trabajador t
        JOIN cargo c           ON c.id_cargo           = t.id_cargo
        JOIN tipos_contrato tc ON tc.id_tipo_contrato  = t.id_tipo_contrato
        JOIN afp a             ON a.id_afp             = t.id_afp
        JOIN sistema_salud s   ON s.id_salud           = t.id_salud
        ORDER BY t.nombre_completo ASC
    ")->fetchAll();

    // Contar activos e inactivos para los badges del filtro
    $totalActivos   = 0;
    $totalInactivos = 0;
    $hoy = date('Y-m-d');
    foreach ($rows as $r) {
        $termino = $r['fecha_termino_contrato'] ?? '';
        if ($termino !== '' && $termino !== null && $termino <= $hoy) {
            $totalInactivos++;
        } else {
            $totalActivos++;
        }
    }

} catch (Throwable $e) {
    http_response_code(500);
    $errMsg = 'Error: ' . $e->getMessage();
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<link rel="stylesheet" href="/remuneraciones/public/assets/css/trabajadores_listado.css" />

<section class="panel">
  <div class="tabs">
    <a class="tab active"  href="/remuneraciones/public/trabajadores_listado.php">Listado Trabajadores</a>
    <a class="tab" href="/remuneraciones/public/trabajadores_nuevo.php">Crear Nuevo Trabajador</a>
  </div>

  <?php if ($okMsg): ?>
    <div class="badge success" style="display:inline-block;margin-bottom:12px">
      <?= htmlspecialchars($okMsg) ?>
    </div>
  <?php endif; ?>

  <?php if ($errMsg): ?>
    <div class="panel" style="border-color:#3b1f1f;background:#1a0f0f;color:#fff">
      <?= htmlspecialchars($errMsg) ?>
    </div>

  <?php elseif (!$rows): ?>
    <p class="lead">Aún no hay registros.</p>

  <?php else: ?>

    <!-- ── Controles: buscador + filtro estado ── -->
    <div class="controles-listado">

      <input type="text" id="input-filtro"
        placeholder="Buscar por nombre o RUT…"
        autocomplete="off">

     <div class="filtro-estado" role="group" aria-label="Filtrar por estado">
         <button type="button" class="btn-estado active" data-filtro="todos">
            Todos
            <span class="count-badge"><?= count($rows) ?></span>
         </button>
            <button type="button" class="btn-estado" data-filtro="activo">
             Activos
            <span class="count-badge count-activos"><?= $totalActivos ?></span>
         </button>
          <button type="button" class="btn-estado" data-filtro="inactivo">
           🔴 No activos
             <span class="count-badge count-inactivos"><?= $totalInactivos ?></span>
          </button>
     </div>
    </div>

    <!-- ── Tabla ── -->
    <div class="table-wrap" style="overflow-x:auto">
      <table class="table" id="tabla-listado">
        <thead>
          <tr>
            <th>Estado</th>
            <th>RUT</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Contrato</th>
            <th>AFP</th>
            <th>Salud</th>
            <th>Colación</th>
            <th>Transporte</th>
            <th>Sueldo base</th>
            <th>Inicio</th>
            <th>Término</th>
          </tr>
        </thead>
        <tbody id="tbody-listado">
          <?php foreach ($rows as $r):
            $termino  = $r['fecha_termino_contrato'] ?? '';
            $inactivo = ($termino !== '' && $termino !== null && $termino <= date('Y-m-d'));
            $estado   = $inactivo ? 'inactivo' : 'activo';
          ?>
          <tr class="fila-listado"
              data-nombre="<?= strtolower(htmlspecialchars($r['nombre_completo'])) ?>"
              data-rut="<?= strtolower(htmlspecialchars($r['rut_trabajador'])) ?>"
              data-estado="<?= $estado ?>">
            <td>
              <?php if ($inactivo): ?>
                <span class="badge-estado inactivo">🔴 No activo</span>
              <?php else: ?>
                <span class="badge-estado activo"> Activo</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($r['rut_trabajador']) ?></td>
            <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($r['nombre_cargo']) ?></td>
            <td><?= htmlspecialchars($r['nombre_contrato']) ?></td>
            <td><?= htmlspecialchars($r['nombre_afp']) ?></td>
            <td><?= htmlspecialchars($r['nombre_salud']) ?></td>
            <td>$<?= number_format((float)$r['colacion'],         0, ',', '.') ?></td>
            <td>$<?= number_format((float)$r['transporte'],       0, ',', '.') ?></td>
            <td>$<?= number_format((float)$r['sueldo_base_fijo'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($r['fecha_inicio_contrato']) ?></td>
            <td><?= $termino ? htmlspecialchars($termino) : '—' ?></td>

          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <p id="sin-resultados-listado" style="display:none;color:var(--muted);margin-top:8px">
      Sin resultados para esa búsqueda.
    </p>

  <?php endif; ?>

  <!-- ── Botón Inicio abajo a la derecha ── -->
  <div class="actions" style="margin-top:24px">
         <a class="btn ghost  btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
  </div>

</section>

<script>
(function () {

  var filas         = document.querySelectorAll('.fila-listado');
  var sinResultados = document.getElementById('sin-resultados-listado');
  var filtroTexto   = '';
  var filtroEstado  = 'todos';

  var panel     = document.querySelector('.panel');
  var tableWrap = document.querySelector('.table-wrap');

  window.addEventListener('load', function () {

    /* ── Fijar tamaño del panel ── */
    if (panel) {
      panel.style.minHeight = panel.offsetHeight + 'px';
      panel.style.minWidth  = panel.offsetWidth  + 'px';
    }

    /* ── Scroll vertical si hay 15+ trabajadores ── */
    if (tableWrap && filas.length >= 15) {
      var alturaCabecera = document.querySelector('.table thead').offsetHeight;
      var alturaFila     = document.querySelector('.fila-listado').offsetHeight;
      var alturaMaxima   = alturaCabecera + (alturaFila * 14) + 10; /* muestra 14 filas + cabecera */
      tableWrap.style.maxHeight = alturaMaxima + 'px';
      tableWrap.style.overflowY = 'auto';
      tableWrap.style.overflowX = 'auto';
    }

  });

/* ── Aplicar filtros combinados ── */
(function () {
  var filas = document.querySelectorAll('.fila-listado');
  var inputFiltro = document.getElementById('input-filtro');
  var botonesEstado = document.querySelectorAll('.btn-estado'); // Solo los botones de filtro
  var filtroEstadoActual = 'todos';

  function aplicarFiltros() {
    var q = inputFiltro ? inputFiltro.value.toLowerCase().trim() : '';
    var visibles = 0;

    filas.forEach(function (f) {
      // 1. Filtro por texto (Nombre o RUT)
      var matchTexto = q === '' || 
                       f.dataset.nombre.includes(q) || 
                       f.dataset.rut.includes(q);

      // 2. Filtro por estado (activo/inactivo)
      var matchEstado = filtroEstadoActual === 'todos' || 
                        f.dataset.estado === filtroEstadoActual;

      if (matchTexto && matchEstado) {
        f.style.display = '';
        visibles++;
      } else {
        f.style.display = 'none';
      }
    });

    // Mostrar/ocultar mensaje de "Sin resultados"
    var sinResultados = document.getElementById('sin-resultados-listado');
    if (sinResultados) {
      sinResultados.style.display = (visibles === 0) ? 'block' : 'none';
    }
  }

  // Evento para el buscador
  if (inputFiltro) {
    inputFiltro.addEventListener('input', aplicarFiltros);
  }

  // Evento para los botones de Activo/Inactivo/Todos
  botonesEstado.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var filtro = this.dataset.filtro;
      if (!filtro) return;

      filtroEstadoActual = filtro;

      // Manejo de clases visuales
      botonesEstado.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      aplicarFiltros();
    });
  });
})();
})();
</script>


<?php include __DIR__ . '/includes/footer.php'; ?>