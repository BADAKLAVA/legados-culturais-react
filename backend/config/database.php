<?php

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$pdo = new PDO(
    "pgsql:host=" . $_ENV["DB_HOST"] .
    ";dbname=" . $_ENV["DB_NAME"] .
    ";sslmode=require",
    $_ENV["DB_USER"],
    $_ENV["DB_PASSWORD"]
);
?>