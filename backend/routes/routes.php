<?php

session_start();

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/controller/UsuarioController.php';

$controller = new UsuarioController($pdo);

//armazenar metodo de requesicao 
$method = $_SERVER['REQUEST_METHOD'];
//armazenar parte da url
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = $_GET['rota'] ?? '';

if ($uri !== "/routes.php") {
    http_response_code(404);
    echo json_encode(["erro" => "Rota não encontrada"]);
    exit;
}

if ($method === "POST" && $rota === "cadastro") {
    $controller->cadastrar();
    exit;
}
elseif ($method === "POST" && $rota === "login") {
    $controller->login();
    exit;
}

http_response_code(404);
header("Content-Type: application/json; charset=UTF-8");
echo json_encode(["erro" => "Operação não encontrada"]);

?>