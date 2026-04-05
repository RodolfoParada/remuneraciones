<?php
// public/bienvenida.php
require_once __DIR__ . '/../src/Auth.php';
Auth::requireLogin();
$authUser = Auth::user();

$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath   = dirname($scriptPath);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '/';
} else {
    $basePath = rtrim($basePath, '/') . '/';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Bienvenido — Sistema de Remuneraciones</title>
  <script>
    if (localStorage.getItem('theme') === 'light') {
      document.documentElement.classList.add('light-mode');
    }
  </script>
  <style>
    /* ── Variables ── */
    :root {
      --bg:      #0f172a;
      --panel:   #111827;
      --border:  #1f2937;
      --txt:     #e5e7eb;
      --muted:   #94a3b8;
      --primary: #2563eb;
    }
    html.light-mode {
      --bg:      #f3f4f6;
      --panel:   #ffffff;
      --border:  #d1d5db;
      --txt:     #1f2937;
      --muted:   #6b7280;
      --primary: #1e40af;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      background: var(--bg);
      color: var(--txt);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      transition: background 0.3s, color 0.3s;
    }

    /* ── Pantalla completa centrada ── */
    .welcome-screen {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0;
      position: relative;
      overflow: hidden;
    }

    /* ── Fondo decorativo ── */
    .welcome-screen::before {
      content: '';
      position: absolute;
      width: 600px; height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(37,99,235,0.12) 0%, transparent 70%);
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    /* ── Logo / Ícono ── */
    .welcome-logo {
      width: 80px; height: 80px;
      border-radius: 22px;
      background: linear-gradient(135deg, #2563eb, #7c3aed);
      display: flex; align-items: center; justify-content: center;
      font-size: 36px;
      box-shadow: 0 12px 40px rgba(37,99,235,0.4);
      margin-bottom: 32px;
      animation: logoIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    /* ── Textos ── */
    .welcome-title {
      font-size: 2.4rem;
      font-weight: 800;
      color: var(--txt);
      text-align: center;
      letter-spacing: -0.03em;
      line-height: 1.15;
      margin-bottom: 12px;
      animation: textIn 0.5s ease 0.2s both;
    }
    .welcome-subtitle {
      font-size: 1rem;
      color: var(--muted);
      text-align: center;
      margin-bottom: 48px;
      animation: textIn 0.5s ease 0.35s both;
    }

    /* ── Saludo usuario ── */
    .welcome-user {
      font-size: 0.9rem;
      color: var(--muted);
      text-align: center;
      margin-bottom: 48px;
      animation: textIn 0.5s ease 0.45s both;
    }
    .welcome-user strong {
      color: var(--primary);
      font-weight: 600;
    }

    /* ── Barra de progreso ── */
    .progress-wrap {
      width: 220px;
      height: 4px;
      background: var(--border);
      border-radius: 99px;
      overflow: hidden;
      animation: textIn 0.4s ease 0.5s both;
    }
    .progress-bar {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--primary), #7c3aed);
      border-radius: 99px;
      animation: fillBar 2.8s ease 0.6s forwards;
    }
    .progress-label {
      font-size: 0.78rem;
      color: var(--muted);
      margin-top: 12px;
      text-align: center;
      animation: textIn 0.4s ease 0.6s both;
    }

    /* ── Animaciones ── */
    @keyframes logoIn {
      from { opacity: 0; transform: scale(0.6); }
      to   { opacity: 1; transform: scale(1); }
    }
    @keyframes textIn {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fillBar {
      from { width: 0%; }
      to   { width: 100%; }
    }
  </style>
</head>
<body>

<div class="welcome-screen">
  <!-- <div class="welcome-logo">💼</div> -->

  <h1 class="welcome-title">Sistema de<br>Remuneraciones</h1>
  <p class="welcome-subtitle">Gestión de trabajadores y liquidaciones</p>

  <p class="welcome-user">
    Bienvenido, <strong><?= htmlspecialchars($authUser['nombre']) ?></strong>
  </p>

  <div class="progress-wrap">
    <div class="progress-bar"></div>
  </div>
  <p class="progress-label">Cargando el sistema...</p>
</div>

<script>
  // Redirige al index después de que la barra termina (2.8s animación + 0.6s delay = 3.4s)
  setTimeout(function () {
    window.location.href = '<?= htmlspecialchars($basePath) ?>index.php';
  }, 3400);
</script>

</body>
</html>