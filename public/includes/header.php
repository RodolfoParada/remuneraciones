<?php
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

  <script>
    if (localStorage.getItem('theme') === 'light') {
      document.documentElement.classList.add('light-mode');
    }
  </script>

  <style>
    /* Forzar visibilidad del botón en la topbar */
    #theme-toggle {
      all: unset;                        /* resetea TODO herencia del browser */
      cursor: pointer;
      font-size: 18px;
      line-height: 1;
      padding: 6px 10px;
      margin: 0 6px;
      border-radius: 8px;
      border: 1px solid rgba(255,255,255,0.35);
      color: #e5e7eb !important;
      background: transparent;
      display: inline-flex;
      align-items: center;
    }
    #theme-toggle:hover {
      background: rgba(255,255,255,0.1) !important;
    }
    html.light-mode #theme-toggle {
      border-color: rgba(0,0,0,0.25) !important;
      color: #1f2937 !important;
    }
    html.light-mode #theme-toggle:hover {
      background: rgba(0,0,0,0.07) !important;
    }
  </style>
</head>
<body>

<header class="topbar">
  <div class="brand">Sistema de Remuneraciones</div>
  <nav class="nav">
    <a href="<?= htmlspecialchars($basePath) ?>index.php">Inicio</a>
    <a href="<?= htmlspecialchars($basePath) ?>trabajadores_listado.php">Trabajadores</a>
    <a href="<?= htmlspecialchars($basePath) ?>listado_liquidaciones.php">Liquidaciones</a>
    <a href="<?= htmlspecialchars($basePath) ?>trabajadores_nuevo.php">Nuevo Trabajador</a>
    <button id="theme-toggle">🌙</button>
  </nav>
</header>

<main class="container">

<script>
  (function () {
    var root = document.documentElement;
    var btn  = document.getElementById('theme-toggle');
    if (!btn) { console.error('theme-toggle no encontrado'); return; }

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