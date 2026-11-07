<?php

session_start();

//arquivos necessarios
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/service/EmailService.php';

//controllers
require_once dirname(__DIR__) . '/controller/UsuarioController.php';
require_once dirname(__DIR__) . '/controller/RecuperacaoSenhaController.php';
require_once dirname(__DIR__) . '/controller/RepositorioController.php';

//models
require_once dirname(__DIR__) . '/model/Usuario.php';
require_once dirname(__DIR__) . '/model/Cliente.php';
require_once dirname(__DIR__) . '/model/Administrador.php';
require_once dirname(__DIR__) . '/model/RecuperacaoSenha.php';
require_once dirname(__DIR__) . '/model/Repositorio.php';
require_once dirname(__DIR__) . '/model/Tag.php';

header("Content-Type: application/json; charset=UTF-8");

$controller = null; 

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = $_GET['rota'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? '';

error_log("Método recebido: " . $method);
error_log("URI recebida: " . $uri);
error_log("Rota recebida: " . $rota);
error_log("Tipo: " . $tipo);
error_log("Id: " . $id);

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
    error_log("Fim de um if");
    if ($rota === "login") {
        error_log("Rota login");
        if($tipo === "cliente") {
            error_log("Entrando no control para logar o cliente");
            $controller = new UsuarioController(new Cliente($pdo));
        }
        if($tipo === "administrador") {
            error_log("Entrando no control para logar o adm");
            $controller = new UsuarioController(new Administrador($pdo));
        }
        $controller->login();
        exit;
    }
    if($rota === "solicitar-recuperacao" || $rota === "redefinir-senha") {
        $recuperacaoSenhaController = new RecuperacaoSenhaController(new Usuario($pdo), new RecuperacaoSenha($pdo), new EmailService(), $pdo);

        if($rota === "solicitar-recuperacao") {
            $recuperacaoSenhaController->solicitarRecuperacao();
            exit;
        }
        if($rota === "redefinir-senha") {
            $recuperacaoSenhaController->redefinirSenha();
            exit;
        }
    }
    if($rota === "criar-repositoro") {
        error_log("Criar repositorio");
        $repositorioController = new RepositorioController(new Repositorio($pdo), new Tag($pdo));
        $repositorioController->criar();
        exit;
    }
}
//GET
if($method === "GET") {
    if($rota === "exibirRepositorios") {
        error_log("Para criar objetos de $rota");
        $repositorioController = new RepositorioController(new Repositorio($pdo), new Tag($pdo));
        error_log("Criou os objetos");
        $repositorioController->exibir();
    }
    exit;
}

http_response_code(404);
echo json_encode(["erro" => "Operação não encontrada"]);
