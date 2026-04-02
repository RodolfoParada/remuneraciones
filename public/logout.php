<?php
// public/logout.php
require_once __DIR__ . '/../src/Auth.php';
Auth::logout();
header('Location: /remuneraciones/public/login.php');
exit;
 