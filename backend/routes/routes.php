<?php //script de rota para executar acoes
require_once "config/database.php";
require_once 'controller/UsuarioController.php';

$controller = new UsuarioController($pdo);

//armazenar metodo de requesicao 
$method = $_SERVER['REQUEST_METHOD'];
//armazenar parte da url
$uri = parse_url($_SERVER['REQUEST_URL'], PHP_URL_PATH);

//rotacionar para cadastro (create)
if($method === "POST" && $uri === "/usurios/cadastrar") {
    $controller->cadastrar();
}
//rotacionar para login ()
elseif ($uri === "/usuarios/login") {
    $controller->login();
}
?>