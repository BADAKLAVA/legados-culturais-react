<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$env = static function (string $nome, ?string $padrao = null): ?string {
    $valor = $_ENV[$nome] ?? $_SERVER[$nome] ?? getenv($nome);
    if ($valor === false || $valor === null || $valor === '') return $padrao;
    return (string)$valor;
};

$host = $env('DB_HOST');
$nome = $env('DB_NAME');
$usuario = $env('DB_USER');
$senha = $env('DB_PASSWORD');
$porta = $env('DB_PORT', '5432');
$sslmode = $env('DB_SSLMODE', 'require');

if (!$host || !$nome || !$usuario || $senha === null) {
    throw new RuntimeException('Configure DB_HOST, DB_NAME, DB_USER e DB_PASSWORD nas variáveis de ambiente.');
}

$pdo = new PDO(
    "pgsql:host={$host};port={$porta};dbname={$nome};sslmode={$sslmode}",
    $usuario,
    $senha,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

// Os campos de data do schema são TIMESTAMP sem fuso; mantenha o horário do sistema em São Paulo.
$pdo->exec("SET TIME ZONE 'America/Sao_Paulo'");
