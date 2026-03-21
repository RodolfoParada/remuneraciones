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
               t.sueldo_base_fijo, t.fecha_inicio_contrato, t.fecha_termino_contrato
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
    <a class="tab" href="/remuneraciones/public/trabajadores_nuevo.php">Nuevo Trabajador</a>
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
        <button type="button" class="btn-estado activo-sel" data-filtro="todos">
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
  </div>
</div>

<style>
  /* ── Controles superiores ── */
  .controles-listado {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-bottom: 14px;
  }

  #input-filtro {
    flex: 1;
    min-width: 200px;
    max-width: 340px;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--input-bg, #0e1628);
    color: var(--txt);
    font: inherit;
    transition: background .3s, border-color .3s, color .3s;
  }
  /* Modo claro: fondo blanco roto */
  html.light-mode #input-filtro {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #1f2937;
  }
  #input-filtro::placeholder { color: var(--muted); }

  /* ── Botones filtro de estado ── */
  .filtro-estado { display: flex; gap: 6px; flex-wrap: wrap; }

  .btn-estado {
    background: var(--input-bg, #0e1628);
    border: 1px solid var(--border);
    color: var(--txt);
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    font: inherit;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .2s, border-color .2s, color .2s;
  }
  .btn-estado:hover { border-color: var(--primary); }
  .btn-estado.activo-sel {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
  }

  /* Modo claro: fondo gris claro para botones no seleccionados */
  html.light-mode .btn-estado:not(.activo-sel) {
    background: #f1f5f9;
    border-color: #d1d5db;
    color: #1f2937;
  }
  html.light-mode .btn-estado:not(.activo-sel):hover {
    border-color: var(--primary);
    background: #e0e7ff;
  }

  /* Badge contador dentro del botón */
  .count-badge {
    border-radius: 999px;
    padding: 1px 7px;
    font-size: 11px;
    font-weight: 700;
    background: rgba(255,255,255,.25);
    color: inherit;
  }
  .btn-estado:not(.activo-sel) .count-badge {
    background: var(--border);
    color: var(--txt);
  }
  html.light-mode .btn-estado:not(.activo-sel) .count-badge {
    background: #d1d5db;
    color: #374151;
  }

  /* ── Badges de estado en la tabla ── */
  .badge-estado {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
  }

  /* Modo oscuro */
  .badge-estado.activo   { background: #052e1a; color: #86efac; border: 1px solid #0c3b24; }
  .badge-estado.inactivo { background: #3b1f1f; color: #fca5a5; border: 1px solid #7f1d1d; }

  /* Modo claro: fondos suaves con texto oscuro legible */
  html.light-mode .badge-estado.activo {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
  }
  html.light-mode .badge-estado.inactivo {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
  }


  /* Fila inactiva: ligeramente atenuada */
  .fila-listado[data-estado="inactivo"] { opacity: .8; }
</style>

<script>
(function () {

  var filas         = document.querySelectorAll('.fila-listado');
  var sinResultados = document.getElementById('sin-resultados-listado');
  var filtroTexto   = '';
  var filtroEstado  = 'todos';

  /* ── Aplicar filtros combinados ── */
  function aplicarFiltros() {
    var visibles = 0;
    filas.forEach(function (fila) {
      var textoOk  = filtroTexto === '' ||
                     fila.dataset.nombre.includes(filtroTexto) ||
                     fila.dataset.rut.includes(filtroTexto);
      var estadoOk = filtroEstado === 'todos' || fila.dataset.estado === filtroEstado;
      var mostrar  = textoOk && estadoOk;
      fila.style.display = mostrar ? '' : 'none';
      if (mostrar) visibles++;
    });
    if (sinResultados)
      sinResultados.style.display = visibles === 0 ? 'block' : 'none';
  }

  /* ── Buscador de texto ── */
  var inputFiltro = document.getElementById('input-filtro');
  if (inputFiltro) {
    inputFiltro.addEventListener('input', function () {
      filtroTexto = this.value.toLowerCase().trim();
      aplicarFiltros();
    });
  }

  /* ── Botones de estado ── */
  document.querySelectorAll('.btn-estado').forEach(function (btn) {
    btn.addEventListener('click', function () {
      filtroEstado = this.dataset.filtro;
      document.querySelectorAll('.btn-estado').forEach(function (b) {
        b.classList.remove('activo-sel');
      });
      this.classList.add('activo-sel');
      aplicarFiltros();
    });
  });


})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>