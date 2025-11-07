<?php
// LÓGICA CRUCIAL PARA CALCULAR LA RUTA BASE DINÁMICAMENTE:
// Esto asegura que el sistema funcione tanto si el proyecto está en localhost/
// como si está en localhost/subcarpeta/, resolviendo el problema de las rutas.
// --- INICIO CÁLCULO RUTA BASE ---
$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptPath);

// Limpiar y estandarizar la ruta:
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '/';
} else {
    // Aseguramos que la ruta tenga una barra al final para concatenar archivos
    $basePath = rtrim($basePath, '/') . '/';
}
// --- FIN CÁLCULO RUTA BASE ---

$pageTitle = $pageTitle ?? 'Sistema de Remuneraciones';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- CSS CORREGIDO: Usa la $basePath dinámica. Se asume que 'assets' 
       está en la misma carpeta que el index.php (o en la raíz del proyecto). -->
  <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/app.css" />
</head>
<body>
  <header class="topbar">
    <div class="brand">Sistema de Remuneraciones</div>
    <nav class="nav">
      <!-- RUTAS CORREGIDAS: Usa la $basePath en todos los enlaces. -->
      <a href="<?= htmlspecialchars($basePath) ?>index.php">Inicio</a>
      <a href="<?= htmlspecialchars($basePath) ?>trabajadores_listado.php">Trabajadores</a>
      <a href="<?= htmlspecialchars($basePath) ?>listado_liquidaciones.php">Liquidaciones</a>
      <a class="cta" href="<?= htmlspecialchars($basePath) ?>trabajadores_nuevo.php">Nuevo Trabajador</a>
    </nav>
  </header>
  <main class="container">