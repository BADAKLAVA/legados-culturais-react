<?php //script de rota para executar acoes

require_once 'controller/UsuarioController.php';

//armazenar metodo de requesicao 
$method = $_SERVER['REQUEST_METHOD'];
//armazenar parte da url
$uri = parse_url($_SERVER['REQUEST_URL'], PHP_URL_PATH)
?>