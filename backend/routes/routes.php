<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/service/EmailService.php';
require_once dirname(__DIR__) . '/controller/UsuarioController.php';
require_once dirname(__DIR__) . '/controller/RecuperacaoSenhaController.php';
require_once dirname(__DIR__) . '/controller/RepositorioController.php';
require_once dirname(__DIR__) . '/service/GoogleDriveStorage.php';
require_once dirname(__DIR__) . '/model/Usuario.php';
require_once dirname(__DIR__) . '/model/Cliente.php';
require_once dirname(__DIR__) . '/model/Administrador.php';
require_once dirname(__DIR__) . '/model/RecuperacaoSenha.php';
require_once dirname(__DIR__) . '/model/Repositorio.php';
require_once dirname(__DIR__) . '/model/Tag.php';
require_once dirname(__DIR__) . '/controller/AvaliacaoController.php';
require_once dirname(__DIR__) . '/model/Avaliacao.php';
require_once dirname(__DIR__) . '/model/Analytics.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

// Nunca devolver HTML de erro: a API sempre responde JSON.
ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e): void {
    error_log('Erro não tratado: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno do servidor.'], JSON_UNESCAPED_UNICODE);
});

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
$avaliacaoController = new AvaliacaoController(new Avaliacao($pdo));
$analytics = new Analytics($pdo);

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
                'descricao' => $linha['descricao'],
                'nome_arquivo' => $linha['nome_arquivo'],
                'tipo_arquivo' => $linha['tipo_arquivo']
            ];
        }
        responder(['sucesso' => true, 'data' => array_values($grupos)]);
    }

    if ($rota === 'download-repositorio') {
        exigirLogin();
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $repositorioModel = new Repositorio($pdo);
        $repo = $repositorioModel->obterRepositorio($id);
        if (!$repo) responder(['sucesso' => false, 'erro' => 'Repositório não encontrado.'], 404);

        $caminho = (string)($repo['filePath'] ?? '');
        $nome = (string)($repo['fileName'] ?: 'arquivo' . ($repo['fileType'] ?? ''));
        $nome = str_replace(["\r", "\n", '"'], ['', '', ''], $nome);
        $mimePorExtensao = [
            '.txt' => 'text/plain',
            '.pdf' => 'application/pdf',
            '.doc' => 'application/msword',
            '.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            '.odt' => 'application/vnd.oasis.opendocument.text',
            '.png' => 'image/png',
            '.jpg' => 'image/jpeg',
            '.jpeg' => 'image/jpeg',
            '.gif' => 'image/gif',
            '.webp' => 'image/webp'
        ];

        if (str_starts_with($caminho, 'gdrive:')) {
            $fileId = substr($caminho, 7);
            if ($fileId === '') responder(['sucesso' => false, 'erro' => 'O arquivo não está disponível para download.'], 404);
            try {
                $conteudo = (new GoogleDriveStorage())->download($fileId);
            } catch (Throwable $e) {
                error_log('Erro ao baixar arquivo do Google Drive: ' . $e->getMessage());
                responder(['sucesso' => false, 'erro' => 'Não foi possível recuperar o arquivo no Google Drive.'], 502);
            }

            if (!$repositorioModel->registrarDownload((int)$_SESSION['id_usuario'], $id)) {
                responder(['sucesso' => false, 'erro' => 'Não foi possível registrar o download.'], 500);
            }

            header('Content-Type: ' . ($mimePorExtensao[strtolower((string)$repo['fileType'])] ?? 'application/octet-stream'));
            header('Content-Length: ' . strlen($conteudo));
            header('X-Content-Type-Options: nosniff');
            header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($nome));
            echo $conteudo;
            exit;
        }

        // Compatibilidade: permite baixar arquivos locais de repositórios criados antes da integração.
        $base = realpath(dirname(__DIR__) . '/uploads/repositorios');
        $arquivo = realpath(dirname(__DIR__) . '/' . ltrim($caminho, '/'));
        if (!$base || !$arquivo || !str_starts_with($arquivo, $base . DIRECTORY_SEPARATOR) || !is_file($arquivo)) {
            responder(['sucesso' => false, 'erro' => 'O arquivo não está disponível para download.'], 404);
        }
        if (!$repositorioModel->registrarDownload((int)$_SESSION['id_usuario'], $id)) {
            responder(['sucesso' => false, 'erro' => 'Não foi possível registrar o download.'], 500);
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($arquivo) : 'application/octet-stream';
        header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($arquivo));
        header('X-Content-Type-Options: nosniff');
        header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($nome));
        readfile($arquivo);
        exit;
    }
    if ($rota === 'exibirComentarios') {
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $avaliacaoController->listar($id, sessaoAutenticada() ? (int)$_SESSION['id_usuario'] : null);
        exit;
    }

    if ($rota === 'comentarioDestaque') {
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $avaliacaoController->exibirDestaque($id);
        exit;
    }

    if ($rota === 'comentariosDestaque') {
        $avaliacaoController->exibirDestaques();
        exit;
    }

    if ($rota === 'relatorio-site') {
        exigirAdmin();
        $relatorio = $analytics->obterRelatorio();
        if ($relatorio === null) responder(['sucesso' => false, 'erro' => 'Não foi possível gerar o relatório. Verifique se as migrações do banco foram aplicadas.'], 500);
        responder(['sucesso' => true, 'data' => $relatorio]);
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
        // O tipo da conta vem exclusivamente do banco de dados.
        $controller = new UsuarioController(new Usuario($pdo), 'us', $analytics);
        $controller->login();
        exit;
    }

    if ($rota === 'registrar-acesso') {
        $dados = json_decode(file_get_contents('php://input'), true);
        $dados = is_array($dados) ? $dados : [];
        $evento = ($dados['evento'] ?? '') === 'heartbeat' ? 'heartbeat' : 'visualizacao';
        $pagina = (string)($dados['pagina'] ?? '/');
        // Aceita somente caminhos locais, sem query string ou URL externa.
        $pagina = parse_url($pagina, PHP_URL_PATH) ?: '/';
        if (!str_starts_with($pagina, '/')) $pagina = '/';
        $ok = $analytics->registrarEvento(
            $evento,
            $pagina,
            sessaoAutenticada() ? (int)$_SESSION['id_usuario'] : null,
            Analytics::hashSessaoAtual()
        );
        responder(['sucesso' => $ok], $ok ? 200 : 500);
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
    if ($rota === 'criar-comentario') {
        exigirLogin();
        $avaliacaoController->criar((int)$_SESSION['id_usuario']);
        exit;
    }

    if ($rota === 'reagir-comentario') {
        exigirLogin();
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $avaliacaoController->reagir($id, (int)$_SESSION['id_usuario']);
        exit;
    }

    if ($rota === 'excluir-comentario') {
        exigirLogin();
        if (!$id) responder(['sucesso' => false, 'erro' => 'Identificador inválido.'], 400);
        $avaliacaoController->excluir($id, (int)$_SESSION['id_usuario'], sessaoEhAdmin());
        exit;
    }
    
    responder(['sucesso' => false, 'erro' => 'Operação não encontrada.'], 404);
}

responder(['sucesso' => false, 'erro' => 'Método não permitido.'], 405);