<?php

// const DB_HOST    = '127.0.0.1';
// const DB_PORT    = 3306;              
// const DB_NAME    = 'remuneraciones';
// const DB_USER    = 'root';
// const DB_PASS    = 'admin';
// const DB_CHARSET = 'utf8mb4';

define('DB_HOST',    getenv('MYSQLHOST')     ?: '127.0.0.1');
define('DB_PORT',    getenv('MYSQLPORT')     ?: 3306);
define('DB_NAME',    getenv('MYSQLDATABASE') ?: 'remuneraciones');
define('DB_USER',    getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: 'admin');
define('DB_CHARSET', 'utf8mb4');