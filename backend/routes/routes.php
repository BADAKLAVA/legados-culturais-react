<?php

session_start();

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/controller/UsuarioController.php';

header("Content-Type: application/json; charset=UTF-8");

$controller = null; 

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = $_GET['rota'] ?? '';
$tipo = $_GET['tipo'] ?? '';

error_log("Método recebido: " . $method);
error_log("URI recebida: " . $uri);
error_log("Rota recebida: " . $rota);

if ($uri !== "/routes.php") {
    http_response_code(404);
    echo json_encode(["erro" => "Rota não encontrada"]);
    exit;
}
//POST
if ($method === "POST") {
    if($rota === "cadastro") {
        if($tipo === "cliente") {
            error_log("Entrou no controller de cadastro do cliente");
            $controller = new UsuarioController(new Cliente($pdo));
        }
        if($tipo === "administrador") {
            error_log("Entrou no controller de cadastro do adm");
            $controller = new UsuarioController(new Administrador($pdo));
        }
        $controller->cadastrar();
         error_log("Saiu do controller de cadastro");
        exit;
    } 
    /*
    if($rota === "login") {
        if($tipo )
    }*/
}

http_response_code(404);
echo json_encode(["erro" => "Operação não encontrada"]);
