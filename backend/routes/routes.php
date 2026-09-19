<?php //script de rota para executar acoes

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "config/database.php";
require_once 'controller/UsuarioController.php';

$controller = new UsuarioController($pdo);

//armazenar metodo de requesicao 
$method = $_SERVER['REQUEST_METHOD'];
//armazenar parte da url
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = $_GET['rota'] ?? '';

if ($method === "POST" && $uri === "/routes.php" && $rota === "cadastro") {
    $controller->cadastrar();
}
elseif ($method === "POST" && $uri === "/routes.php" && $rota === "login") {
    $controller->login();
}
?>