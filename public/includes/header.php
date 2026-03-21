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
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>trabajadores_listado.php">Trabajadores</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>listado_liquidaciones.php">Liquidaciones</a>
    <a class="nav-item" href="<?= htmlspecialchars($basePath) ?>trabajadores_nuevo.php">Nuevo Trabajador</a>
    <button class="nav-item" id="theme-toggle">🌙</button>
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