<?php
// public/admin_perfil.php
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Auth.php';

$pageTitle = 'Mi Perfil';
include __DIR__ . '/includes/header.php';

$pdo     = (new Db())->pdo();
$errores = [];
$ok      = '';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$authUser['id']]);
$usuario = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $actual   = trim($_POST['password_actual'] ?? '');

    if (!$nombre) $errores[] = 'El nombre es requerido.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';

    // Verificar email duplicado
    $chk = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ?");
    $chk->execute([$email, $authUser['id']]);
    if ($chk->fetch()) $errores[] = 'El correo ya está en uso por otro usuario.';

    if ($password) {
        if (!password_verify($actual, $usuario['password_hash']))
            $errores[] = 'La contraseña actual es incorrecta.';
        if (strlen($password) < 8)
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
    }

    if (empty($errores)) {
        if ($password) {
            $pdo->prepare("UPDATE usuarios SET nombre=?,email=?,password_hash=? WHERE id_usuario=?")
                ->execute([$nombre, $email, password_hash($password, PASSWORD_BCRYPT), $authUser['id']]);
        } else {
            $pdo->prepare("UPDATE usuarios SET nombre=?,email=? WHERE id_usuario=?")
                ->execute([$nombre, $email, $authUser['id']]);
        }
        // Refrescar sesión
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_email']  = $email;
        $ok = 'Perfil actualizado correctamente.';
        // Recargar datos
        $stmt->execute([$authUser['id']]);
        $usuario = $stmt->fetch();
    }
}
?>

<div class="page-header">
    <h1>Mi Perfil</h1>
</div>

<?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<?php if ($errores): ?>
<div class="alert alert-error"><?php foreach ($errores as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <form method="POST">
        <div clbel>Correo electrónico</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin:24px 0;">
        <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px;">
            Deja en blanco si no deseas cambiar la contraseña.
        </p>

        <div class="form-group">
            <label>Contraseña actual</label>
            <input type="password" name="password_actual" placeholder="••••••••" autocomplete="current-password">
        </div>
        <div class="form-group">
            <label>Nueva contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
           <a href="<?= $basePath ?>index.php" class="btn btn-ghost">Volver</a>
        </div>
    </form>
</div>ass="form-group">
            <label>Nombre completo</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin:24px 0;">
        <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px;">
            Deja en blanco si no deseas cambiar la contraseña.
        </p>

        <div class="form-group">
            <label>Contraseña actual</label>
            <input type="password" name="password_actual" placeholder="••••••••" autocomplete="current-password">
        </div>
        <div class="form-group">
            <label>Nueva contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          <a href="<?= $basePath ?>index.php" class="btn btn-ghost">Volver</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>