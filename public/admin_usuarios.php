<?php
// public/admin_usuarios.php
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Auth.php';

$pageTitle = 'Gestión de Usuarios';
include __DIR__ . '/includes/header.php';

$pdo     = (new Db())->pdo();
$errores = [];
$ok      = '';

// ── ELIMINAR ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int)($_POST['id_usuario'] ?? 0);
    if ($id === $authUser['id']) {
        $errores[] = 'No puedes eliminar tu propio usuario.';
    } else {
        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id]);
        $ok = 'Usuario eliminado correctamente.';
    }
}

// ── GUARDAR (crear / editar) ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id       = (int)($_POST['id_usuario'] ?? 0);
    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $rol      = $_POST['rol']           ?? 'admin';
    $activo   = (int)($_POST['activo']  ?? 1);
    $password = trim($_POST['password'] ?? '');

    if (!$nombre) $errores[] = 'El nombre es requerido.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';
    if ($id === 0 && !$password) $errores[] = 'La contraseña es requerida para nuevos usuarios.';
    if ($password && strlen($password) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';

    // Verificar email duplicado
    $chk = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ?");
    $chk->execute([$email, $id]);
    if ($chk->fetch()) $errores[] = 'El correo ya está registrado.';

    if (empty($errores)) {
        if ($id > 0) {
            // Actualizar
            $sql = "UPDATE usuarios SET nombre=?, email=?, rol=?, activo=? WHERE id_usuario=?";
            $params = [$nombre, $email, $rol, $activo, $id];
            if ($password) {
                $sql = "UPDATE usuarios SET nombre=?, email=?, rol=?, activo=?, password_hash=? WHERE id_usuario=?";
                $params = [$nombre, $email, $rol, $activo, password_hash($password, PASSWORD_BCRYPT), $id];
            }
            $pdo->prepare($sql)->execute($params);
            $ok = 'Usuario actualizado correctamente.';
        } else {
            // Crear
            $pdo->prepare("INSERT INTO usuarios (nombre,email,password_hash,rol,activo) VALUES (?,?,?,?,?)")
                ->execute([$nombre, $email, password_hash($password, PASSWORD_BCRYPT), $rol, $activo]);
            $ok = 'Usuario creado correctamente.';
        }
    }
}

// ── LISTAR ──────────────────────────────────────────────────
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nombre")->fetchAll();

// ── EDITAR: cargar datos ─────────────────────────────────────
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch() ?: null;
}
?>

<div class="page-header">
    <h1>Gestión de Usuarios</h1>
    <a href="?nuevo=1" class="btn btn-primary">+ Nuevo usuario</a>
</div>

<?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<?php if ($errores): ?>
<div class="alert alert-error"><?php foreach ($errores as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
<?php endif; ?>

<?php if (isset($_GET['nuevo']) || $editando): ?>
<!-- ── FORMULARIO ── -->
<div class="card panel">
    <h2 style="margin-bottom:24px;"><?= $editando ? 'Editar usuario' : 'Nuevo usuario' ?></h2>
    <form method="POST" action="">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id_usuario" value="<?= $editando['id_usuario'] ?? 0 ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($editando['nombre'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" value="<?= htmlspecialchars($editando['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Contraseña <?= $editando ? '(dejar en blanco para no cambiar)' : '' ?></label>
                <input type="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="rol">
                    <option value="admin"      <?= ($editando['rol'] ?? '') === 'admin'      ? 'selected' : '' ?>>Administrador</option>
                    <option value="supervisor" <?= ($editando['rol'] ?? '') === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="activo">
                    <option value="1" <?= ($editando['activo'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= ($editando['activo'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="/remuneraciones/public/admin_usuarios.php" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── LISTADO ── -->
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Último acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($u['rol']) ?></span></td>
                <td>
                    <?php if ($u['activo']): ?>
                        <span class="badge badge-green">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-red">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td><?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : '—' ?></td>
                <td>
                    <a href="?editar=<?= $u['id_usuario'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <?php if ($u['id_usuario'] !== $authUser['id']): ?>
                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                        <input type="hidden" name="accion"     value="eliminar">
                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>