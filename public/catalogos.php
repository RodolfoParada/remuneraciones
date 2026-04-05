<?php
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Auth.php';

$pageTitle = 'Catálogos';
include __DIR__ . '/includes/header.php';

$esAdmin = $authUser['rol'] === 'admin';

$pdo     = (new Db())->pdo();
$errores = [];
$ok      = '';

if ($esAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $tabla  = $_POST['tabla']  ?? '';

    $tablas = [
        'cargo'         => ['id' => 'id_cargo',        'nombre' => 'nombre_cargo',    'extra' => null],
        'tipos_contrato'=> ['id' => 'id_tipo_contrato', 'nombre' => 'nombre_contrato', 'extra' => null],
        'afp'           => ['id' => 'id_afp',           'nombre' => 'nombre_afp',      'extra' => 'porcentaje_descuento'],
        'sistema_salud' => ['id' => 'id_salud',         'nombre' => 'nombre_salud',    'extra' => null],
    ];

    if (!isset($tablas[$tabla])) {
        $errores[] = 'Tabla no válida.';
    } else {
        $cfg    = $tablas[$tabla];
        $nombre = trim($_POST['nombre'] ?? '');
        $extra  = trim($_POST['extra']  ?? '');
        $id     = (int)($_POST['id']    ?? 0);

        if ($accion !== 'eliminar' && !$nombre) {
            $errores[] = 'El nombre es requerido.';
        } elseif ($accion !== 'eliminar' && $cfg['extra'] && !is_numeric($extra)) {
            $errores[] = 'El porcentaje debe ser un número.';
        } else {
            try {
                if ($accion === 'crear') {
                    if ($cfg['extra']) {
                        $pdo->prepare("INSERT INTO {$tabla} ({$cfg['nombre']}, {$cfg['extra']}) VALUES (?, ?)")
                            ->execute([$nombre, $extra]);
                    } else {
                        $pdo->prepare("INSERT INTO {$tabla} ({$cfg['nombre']}) VALUES (?)")
                            ->execute([$nombre]);
                    }
                    $ok = 'Registro creado correctamente.';

                } elseif ($accion === 'editar' && $id > 0) {
                    if ($cfg['extra']) {
                        $pdo->prepare("UPDATE {$tabla} SET {$cfg['nombre']}=?, {$cfg['extra']}=? WHERE {$cfg['id']}=?")
                            ->execute([$nombre, $extra, $id]);
                    } else {
                        $pdo->prepare("UPDATE {$tabla} SET {$cfg['nombre']}=? WHERE {$cfg['id']}=?")
                            ->execute([$nombre, $id]);
                    }
                    $ok = 'Registro actualizado correctamente.';

                } elseif ($accion === 'eliminar' && $id > 0) {
                    $pdo->prepare("DELETE FROM {$tabla} WHERE {$cfg['id']}=?")
                        ->execute([$id]);
                    $ok = 'Registro eliminado correctamente.';
                }
            } catch (Throwable $e) {
                $errores[] = 'Error: ' . $e->getMessage();
            }
        }
    }
}

try {
    $cat['cargo']         = $pdo->query("SELECT id_cargo, nombre_cargo FROM cargo ORDER BY nombre_cargo")->fetchAll();
    $cat['tipos_contrato']= $pdo->query("SELECT id_tipo_contrato, nombre_contrato FROM tipos_contrato ORDER BY nombre_contrato")->fetchAll();
    $cat['afp']           = $pdo->query("SELECT id_afp, nombre_afp, porcentaje_descuento FROM afp ORDER BY nombre_afp")->fetchAll();
    $cat['sistema_salud'] = $pdo->query("SELECT id_salud, nombre_salud FROM sistema_salud ORDER BY nombre_salud")->fetchAll();
} catch (Throwable $e) {
    $errores[] = 'Error de base de datos: ' . $e->getMessage();
}

function renderCatalogo(string $titulo, string $tabla, array $filas, string $colId, string $colNombre, bool $esAdmin, string $colExtra = '', string $labelExtra = ''): void {
?>
<div class="card">
    <div class="cat-header">
        <h3><?= htmlspecialchars($titulo) ?></h3>
        <?php if ($esAdmin): ?>
        <button class="btn small primary" onclick="abrirModal('crear','<?= $tabla ?>','<?= $colExtra ?>','<?= htmlspecialchars($labelExtra) ?>')">+ Agregar</button>
        <?php endif; ?>
    </div>

    <?php if (!$filas): ?>
        <p class="lead">Sin registros.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <?php if ($colExtra): ?><th><?= htmlspecialchars($labelExtra) ?></th><?php endif; ?>
                    <?php if ($esAdmin): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $r): ?>
                <tr>
                    <td><?= (int)$r[$colId] ?></td>
                    <td><?= htmlspecialchars($r[$colNombre]) ?></td>
                    <?php if ($colExtra): ?>
                        <td><?= number_format((float)$r[$colExtra], 2, ',', '.') ?>%</td>
                    <?php endif; ?>
                    <?php if ($esAdmin): ?>
                    <td style="display:flex;gap:6px;">
                        <button class="btn small"
                            onclick="abrirModal('editar','<?= $tabla ?>','<?= $colExtra ?>','<?= htmlspecialchars($labelExtra) ?>',<?= (int)$r[$colId] ?>,'<?= htmlspecialchars(addslashes($r[$colNombre])) ?>','<?= $colExtra ? (float)$r[$colExtra] : '' ?>')">
                            Editar
                        </button>
                        <button class="btn small btn-eliminar"
                            onclick="abrirEliminar('<?= $tabla ?>',<?= (int)$r[$colId] ?>,'<?= htmlspecialchars(addslashes($r[$colNombre])) ?>')">
                            Eliminar
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php } ?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/catalogos.css" />

<section class="panel">
    <div class="tabs">
        <span class="tab active">Catálogos</span>
        <?php if (!$esAdmin): ?>
            <span class="tab-badge">👁 Solo lectura</span>
        <?php endif; ?>
    </div>

    <?php if ($ok): ?>
        <div class="alert-ok" id="alert-ok"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>
    <?php if ($errores): ?>
        <div class="panel panel-error">
            <div class="badge">Se encontraron errores</div>
            <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="grid two">
        <?php renderCatalogo('Cargos',           'cargo',          $cat['cargo'],         'id_cargo',        'nombre_cargo',    $esAdmin); ?>
        <?php renderCatalogo('Tipos de contrato','tipos_contrato', $cat['tipos_contrato'],'id_tipo_contrato','nombre_contrato', $esAdmin); ?>
        <?php renderCatalogo('AFP',              'afp',            $cat['afp'],           'id_afp',          'nombre_afp',      $esAdmin, 'porcentaje_descuento', '% Descuento'); ?>
        <?php renderCatalogo('Sistema de salud', 'sistema_salud',  $cat['sistema_salud'], 'id_salud',        'nombre_salud',    $esAdmin); ?>
    </div>

    <div class="actions">
        <a class="btn ghost btn-volver" href="/remuneraciones/public/index.php">Volver al Inicio</a>
    </div>
</section>

<?php if ($esAdmin): ?>

<!-- ── MODAL CREAR / EDITAR ── -->
<div id="modal-overlay" style="display:none">
    <div id="modal-box">
        <h3 id="modal-title">Agregar registro</h3>
        <form method="POST" id="modal-form">
            <input type="hidden" name="accion" id="modal-accion">
            <input type="hidden" name="tabla"  id="modal-tabla">
            <input type="hidden" name="id"     id="modal-id">

            <label>Nombre
                <input type="text" name="nombre" id="modal-nombre" required autocomplete="off">
            </label>

            <div id="modal-extra-wrap" style="display:none">
                <label id="modal-extra-label">% Descuento
                    <input type="number" name="extra" id="modal-extra" step="0.01" min="0" max="100">
                </label>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn primary">Guardar</button>
                <button type="button" class="btn ghost" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL ELIMINAR ── -->
<div id="modal-eliminar" style="display:none">
    <div id="modal-eliminar-box">
        <div class="modal-eliminar-icon"></div>
        <h3>¿Eliminar registro?</h3>
        <p id="modal-eliminar-texto">Esta acción no se puede deshacer.</p>
        <form method="POST" id="form-eliminar">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="tabla"  id="del-tabla">
            <input type="hidden" name="id"     id="del-id">
            <input type="hidden" name="nombre" id="del-nombre">
            <div class="modal-actions" style="justify-content:center;">
                <button type="submit" class="btn btn-danger-confirm">Sí, eliminar</button>
                <button type="button" class="btn ghost" onclick="cerrarEliminar()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ── Overlay compartido ── */
#modal-overlay,
#modal-eliminar {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Modal crear/editar ── */
#modal-box {
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.4);
}
#modal-box h3 {
    margin: 0 0 20px;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--txt);
}
#modal-box label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 16px;
}
#modal-box input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
}

/* ── Modal eliminar ── */
#modal-eliminar-box {
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 36px 32px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.4);
    text-align: center;
}
.modal-eliminar-icon {
    font-size: 3rem;
    margin-bottom: 14px;
}
#modal-eliminar-box h3 {
    margin: 0 0 10px;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--txt);
}
#modal-eliminar-texto {
    font-size: 0.875rem;
    color: var(--muted);
    margin-bottom: 24px;
}

/* ── Botón eliminar rojo ── */
.btn-eliminar {
    color: #f87171 !important;
    border-color: #f87171 !important;
}
.btn-eliminar:hover {
    background: rgba(248,113,113,0.1) !important;
}
.btn-danger-confirm {
    background: #ef4444;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}
.btn-danger-confirm:hover { background: #dc2626; }

.modal-actions { display: flex; gap: 10px; margin-top: 8px; }

/* ── Otros ── */
.cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.cat-header h3 { margin: 0; }
.tab-badge {
    font-size: 0.78rem;
    color: var(--muted);
    padding: 4px 10px;
    border: 1px solid var(--border);
    border-radius: 20px;
    margin-left: 8px;
}
.alert-ok {
    background: rgba(22,163,74,0.1);
    border: 1px solid rgba(22,163,74,0.3);
    color: #4ade80;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: .875rem;
}
html.light-mode .alert-ok { color: #15803d; }
</style>

<script>
/* ── Modal crear / editar ── */
function abrirModal(accion, tabla, colExtra, labelExtra, id, nombre, extra) {
    document.getElementById('modal-accion').value = accion;
    document.getElementById('modal-tabla').value  = tabla;
    document.getElementById('modal-id').value     = id     || '';
    document.getElementById('modal-nombre').value = nombre || '';
    document.getElementById('modal-title').textContent = accion === 'crear' ? 'Agregar registro' : 'Editar registro';

    var extraWrap = document.getElementById('modal-extra-wrap');
    if (colExtra) {
        document.getElementById('modal-extra-label').childNodes[0].textContent = labelExtra + ' ';
        document.getElementById('modal-extra').value = extra || '';
        extraWrap.style.display = 'block';
        document.getElementById('modal-extra').required = true;
    } else {
        extraWrap.style.display = 'none';
        document.getElementById('modal-extra').required = false;
    }

    document.getElementById('modal-overlay').style.display = 'flex';
    document.getElementById('modal-nombre').focus();
}

function cerrarModal() {
    document.getElementById('modal-overlay').style.display = 'none';
}

document.getElementById('modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

/* ── Modal eliminar ── */
function abrirEliminar(tabla, id, nombre) {
    document.getElementById('del-tabla').value  = tabla;
    document.getElementById('del-id').value     = id;
    document.getElementById('del-nombre').value = nombre;
    document.getElementById('modal-eliminar-texto').textContent =
        'Estás por eliminar "' + nombre + '". Esta acción no se puede deshacer.';
    document.getElementById('modal-eliminar').style.display = 'flex';
}

function cerrarEliminar() {
    document.getElementById('modal-eliminar').style.display = 'none';
}

document.getElementById('modal-eliminar').addEventListener('click', function(e) {
    if (e.target === this) cerrarEliminar();
});
</script>

<?php endif; ?>

<script>
window.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('alert-ok');
    if (el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.6s ease';
            el.style.opacity    = '0';
            setTimeout(function() { el.style.display = 'none'; }, 600);
        }, 3000);
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>