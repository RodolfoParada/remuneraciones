<?php
// public/includes/header.php

// ── AUTENTICACIÓN ──────────────────────────────────────────
require_once __DIR__ . '/../../src/Auth.php';
Auth::requireLogin();   // Si no hay sesión → redirige a login.php
$authUser = Auth::user();
// ──────────────────────────────────────────────────────────

$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath   = dirname($scriptPath);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '/';
} else {
    $basePath = rtrim($basePath, '/') . '/';
}
$pageTitle = $pageTitle ?? 'Sistema de Remuneraciones';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/app.css" />
  <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/header.css" />
  <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/footer.css" />

  <!-- Anti-parpadeo: aplica tema antes de pintar -->
  <script>
    if (localStorage.getItem('theme') === 'light') {
      document.documentElement.classList.add('light-mode');
    }
  </script>
</head>
<body>

<header class="topbar">
  <div class="brand">Sistema de Remuneraciones</div>
  <nav class="nav">
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>index.php">Inicio</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>trabajadores_listado.php">Lista de Trabajadores</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>trabajadores_nuevo.php">Crear Nuevo Trabajador</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>liquidaciones_nueva.php">Crear Nueva Liquidacion</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>listado_liquidaciones.php">Liquidaciones</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>resumen.php">Guía</a>
      <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>parametros.php">Término de contrato</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>catalogos.php">Catálogos</a>
    <button class="nav-item" id="theme-toggle">🌙</button>

    <!-- ── USUARIO / LOGOUT ── -->
    <div class="nav-user">
      <span class="nav-avatar"><?= strtoupper(substr($authUser['nombre'], 0, 1)) ?></span>
      <div class="nav-dropdown">
        <span class="nav-username"><?= htmlspecialchars($authUser['nombre']) ?></span>
        <!-- <a href="<?= htmlspecialchars($basePath) ?>admin_perfil.php">Mi perfil</a> -->
        <!-- <a href="<?= htmlspecialchars($basePath) ?>admin_usuarios.php">Usuarios</a> -->
        <a href="<?= htmlspecialchars($basePath) ?>logout.php" class="nav-logout">Cerrar sesión</a>
      </div>
    </div>
    <!-- ── /USUARIO ── -->
  </nav>
</header>

<main class="container">

<script>
  (function () {
    var root = document.documentElement;
    var btn  = document.getElementById('theme-toggle');
    if (!btn) return;

    function sync() {
      var light       = root.classList.contains('light-mode');
      btn.textContent = light ? 'Modo Claro' : 'Modo Oscuro';
      btn.title       = light ? 'Activar modo oscuro' : 'Activar modo claro';
    }

    sync();

    btn.addEventListener('click', function () {
      var nowLight = root.classList.toggle('light-mode');
      localStorage.setItem('theme', nowLight ? 'light' : 'dark');
      sync();
    });
  })();
</script>

<!--Ocultar scroll en todas las páginas -->
<style>
  html, body { scrollbar-width: none; }
  html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; }

  /* ── Estilos mínimos para el menú de usuario ── */
  .nav-user {
    position: relative;
    display: flex;
    align-items: center;
    margin-left: 8px;
  }
  .nav-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
  }
  .nav-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 10px);
    background: var(--surface, #1e2533);
    border: 1px solid var(--border, #2d3748);
    border-radius: 10px;
    padding: 8px;
    min-width: 170px;
    z-index: 999;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
  }
  .nav-user:hover .nav-dropdown { display: block; }
  .nav-username {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--muted, #7d8590);
    padding: 4px 8px 10px;
    border-bottom: 1px solid var(--border, #2d3748);
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .nav-dropdown a {
    display: block;
    padding: 7px 10px;
    border-radius: 7px;
    text-decoration: none;
    font-size: 0.875rem;
    color: var(--text, #e6edf3);
    transition: background 0.15s;
  }
  .nav-dropdown a:hover { background: rgba(255,255,255,0.06); }
  .nav-logout { color: #f87171 !important; margin-top: 4px; }
</style>