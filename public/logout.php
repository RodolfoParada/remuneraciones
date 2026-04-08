<?php
// public/logout.php
require_once __DIR__ . '/../src/Auth.php';

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '/';
} else {
    $basePath = rtrim($basePath, '/') . '/';
}

Auth::logout();
header('Location: ' . $basePath . 'login.php');
exit;