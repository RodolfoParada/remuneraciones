<?php
require_once __DIR__ . '/../config/env.php';

final class Db {
  private PDO $pdo;

  public function __construct() {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $opts = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
  }

  public function pdo(): PDO {
    return $this->pdo;
  }
}