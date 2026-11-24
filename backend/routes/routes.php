<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/service/EmailService.php';
require_once dirname(__DIR__) . '/controller/UsuarioController.php';
require_once dirname(__DIR__) . '/controller/RecuperacaoSenhaController.php';
require_once dirname(__DIR__) . '/controller/RepositorioController.php';
require_once dirname(__DIR__) . '/model/Usuario.php';
require_once dirname(__DIR__) . '/model/Cliente.php';
require_once dirname(__DIR__) . '/model/Administrador.php';
require_once dirname(__DIR__) . '/model/RecuperacaoSenha.php';
require_once dirname(__DIR__) . '/model/Repositorio.php';
require_once dirname(__DIR__) . '/model/Tag.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function responder(array $dados, int $status = 200): void {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function sessaoAutenticada(): bool {
    $tipo = strtolower(trim((string)($_SESSION['tipo'] ?? '')));
    return isset($_SESSION['id_usuario'])
        && (int)$_SESSION['id_usuario'] > 0
        && in_array($tipo, ['us', 'adm', 'ad', 'administrador'], true);
}

function sessaoEhAdmin(): bool {
    $tipo = strtolower(trim((string)($_SESSION['tipo'] ?? '')));
    return in_array($tipo, ['adm', 'ad', 'administrador'], true);
}

function exigirLogin(): void {
    if (!sessaoAutenticada()) responder(['sucesso' => false, 'erro' => 'Faça login para realizar esta ação.'], 401);
}

function exigirAdmin(): void {
    exigirLogin();
    if (!sessaoEhAdmin()) responder(['sucesso' => false, 'erro' => 'Apenas administradores podem realizar esta ação.'], 403);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$rota = $_GET['rota'] ?? '';
$tipoCadastro = $_GET['tipo'] ?? '';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($uri !== '/routes.php') {
    responder(['sucesso' => false, 'erro' => 'Rota não encontrada.'], 404);
}

$repositorioController = new RepositorioController(new Repositorio($pdo), new Tag($pdo), $pdo);

if ($method === 'GET') {
    if ($rota === 'sessao') {
        responder([
            'sucesso' => true,
            'autenticado' => sessaoAutenticada(),
            'id_usuario' => sessaoAutenticada() ? (int)$_SESSION['id_usuario'] : null,
            'tipo' => sessaoAutenticada() ? (sessaoEhAdmin() ? 'adm' : 'us') : null
        ]);
    }

    if ($rota === 'exibirRepositorios') {
        $repositorioController->exibir();
        exit;
    }

    if ($rota === 'exibirTags') {
        $repositorioController->exibirTags();
        exit;
    }

    if ($rota === 'perfil') {
        exigirLogin();
        $perfil = (new Usuario($pdo))->obterPerfil((int)$_SESSION['id_usuario']);
        if (!$perfil) responder(['sucesso' => false, 'erro' => 'Perfil não encontrado.'], 404);
        responder(['sucesso' => true, 'data' => $perfil]);
    }

    if ($rota === 'downloads') {
        exigirLogin();
        $linhas = (new Usuario($pdo))->listarDownloads((int)$_SESSION['id_usuario']);
        if ($linhas === null) responder(['sucesso' => false, 'erro' => 'Não foi possível carregar o histórico.'], 500);
        $grupos = [];
        foreach ($linhas as $linha) {
            $data = date('d/m/Y', strtotime($linha['data_historico']));
            if (!isset($grupos[$data])) $grupos[$data] = ['data' => $data, 'itens' => []];
            $grupos[$data]['itens'][] = [
                'id' => (int)$linha['id'],
                'titulo' => $linha['titulo'],
                'descricao' => $linha['descricao']
            ];
        }
        responder(['sucesso' => true, 'data' => array_values($grupos)]);
    }

    if ($rota === 'download-repositorio') {
        exigirLogin();
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $repo = (new Repositorio($pdo))->obterRepositorio($id);
        if (!$repo) responder(['sucesso' => false, 'erro' => 'Repositório não encontrado.'], 404);

        $base = realpath(dirname(__DIR__) . '/uploads/repositorios');
        $arquivo = realpath(dirname(__DIR__) . '/' . ltrim((string)$repo['filePath'], '/'));
        if (!$base || !$arquivo || !str_starts_with($arquivo, $base . DIRECTORY_SEPARATOR) || !is_file($arquivo)) {
            responder(['sucesso' => false, 'erro' => 'O arquivo não está disponível para download.'], 404);
        }

        if (!(new Repositorio($pdo))->registrarDownload((int)$_SESSION['id_usuario'], $id)) {
            responder(['sucesso' => false, 'erro' => 'Não foi possível registrar o download.'], 500);
        }

        $nome = (string)($repo['fileName'] ?: basename($arquivo));
        $nome = str_replace(["\r", "\n", '"'], ['', '', ''], $nome);
        $mime = function_exists('mime_content_type') ? mime_content_type($arquivo) : 'application/octet-stream';
        header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($arquivo));
        header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($nome));
        readfile($arquivo);
        exit;
    }

    responder(['sucesso' => false, 'erro' => 'Operação não encontrada.'], 404);
}

if ($method === 'POST') {
    if ($rota === 'cadastro') {
        if ($tipoCadastro === 'cliente') {
            $controller = new UsuarioController(new Cliente($pdo), 'us');
        } elseif ($tipoCadastro === 'administrador') {
            exigirAdmin();
            $controller = new UsuarioController(new Administrador($pdo), 'ad');
        } else {
            responder(['sucesso' => false, 'erro' => 'Tipo de cadastro inválido.'], 400);
        }
        $controller->cadastrar();
        exit;
    }

    if ($rota === 'login') {
        if ($tipoCadastro === 'cliente') {
            $controller = new UsuarioController(new Cliente($pdo), 'us');
        } elseif ($tipoCadastro === 'administrador') {
            //exigirAdmin();
            $controller = new UsuarioController(new Administrador($pdo), 'ad');
        } else {
            responder(['sucesso' => false, 'erro' => 'Tipo de conta inválido.'], 400);
        }
        $controller->login();
        exit;
    }

    if ($rota === 'solicitar-recuperacao' || $rota === 'redefinir-senha') {
        $controller = new RecuperacaoSenhaController(new Usuario($pdo), new RecuperacaoSenha($pdo), new EmailService(), $pdo);
        if ($rota === 'solicitar-recuperacao') $controller->solicitarRecuperacao();
        else $controller->redefinirSenha();
        exit;
    }

    if ($rota === 'criar-repositorio') {
        exigirAdmin();
        $repositorioController->criar();
        exit;
    }

    if ($rota === 'editar-repositorio') {
        exigirAdmin();
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $repositorioController->editar($id);
        exit;
    }

    if ($rota === 'excluir-repositorio') {
        exigirAdmin();
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $repositorioController->excluir($id);
        exit;
    }

    if ($rota === 'criar-tag') {
        exigirAdmin();
        $repositorioController->criarTagGlobal();
        exit;
    }

    if ($rota === 'atualizar-perfil') {
        exigirLogin();
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) responder(['sucesso' => false, 'erro' => 'Dados inválidos.'], 400);
        if (($dados['sexo'] ?? '') === 'Masculino') $dados['sexo'] = 'm';
        if (($dados['sexo'] ?? '') === 'Feminino') $dados['sexo'] = 'f';
        $ok = (new Usuario($pdo))->atualizarPerfil((int)$_SESSION['id_usuario'], $dados);
        if (!$ok) responder(['sucesso' => false, 'erro' => 'Não foi possível atualizar o perfil.'], 400);
        responder(['sucesso' => true, 'message' => 'Perfil atualizado com sucesso.']);
    }

    if ($rota === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        responder(['sucesso' => true]);
    }

    responder(['sucesso' => false, 'erro' => 'Operação não encontrada.'], 404);
}

responder(['sucesso' => false, 'erro' => 'Método não permitido.'], 405);
