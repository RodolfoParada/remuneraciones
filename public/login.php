<?php
// public/login.php
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Auth.php';

// Si ya está logueado, redirigir al inicio
if (Auth::check()) {
    header('Location: /remuneraciones/public/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Por favor completa todos los campos.';
    } else {
        try {
            $pdo  = (new Db())->pdo();
            $stmt = $pdo->prepare(
                "SELECT * FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1"
            );
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                // Actualizar último acceso
                $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id")
                    ->execute([':id' => $usuario['id_usuario']]);

                Auth::login($usuario);
                header('Location: /remuneraciones/public/index.php');
                exit;
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        } catch (Throwable $e) {
            $error = 'Error de conexión. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión — Remuneraciones</title>
    <link rel="stylesheet" href="/remuneraciones/public/assets/css/login.css" />
</head>
<body>
    <div class="login-bg">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">R</div>
                <h1>Sistema de Remuneraciones</h1>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@empresa.cl"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        autocomplete="email"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        />
                        <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Mostrar contraseña">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-text">Ingresar</span>
                    <span class="btn-loading" style="display:none">Verificando...</span>
                </button>
            </form>
        </div>
    </div>

    <script src="/remuneraciones/public/assets/js/login.js"></script>
</body>
</html>