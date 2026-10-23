<?php

session_start();

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/controller/UsuarioController.php';

header("Content-Type: application/json; charset=UTF-8");

$controller = new UsuarioController($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = $_GET['rota'] ?? '';

error_log("Método recebido: " . $method);
error_log("URI recebida: " . $uri);
error_log("Rota recebida: " . $rota);

if ($uri !== "/routes.php") {
    http_response_code(404);
    echo json_encode(["erro" => "Rota não encontrada"]);
    exit;
}
if ($method === "POST" && $rota === "cadastro") {
    error_log("Entrou no controller de cadastro");

    $controller->cadastrar();

    error_log("Saiu do controller de cadastro");
    exit;
}/*
if ($method === "POST" && $rota === "cadastro") {
    $controller->cadastrar();
    exit;
} */
if ($method === "POST" && $rota === "login") {
    $controller->login();
    exit;
}

http_response_code(404);
echo json_encode(["erro" => "Operação não encontrada"]);
