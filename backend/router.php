
<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = '/app';

// Encaminha a API para o arquivo correto.
if ($uri === '/routes.php') {
    require __DIR__ . '/routes/routes.php';
    return true;
}

// Encaminha teste.php, caso seja acessado pela URL.
if ($uri === '/teste.php') {
    require __DIR__ . '/teste.php';
    return true;
}

// Deixa o servidor PHP entregar arquivos estáticos existentes.
$file = realpath($root . $uri);

if (
    $file !== false &&
    str_starts_with($file, $root . '/') &&
    is_file($file)
) {
    return false;
}

// Não retorna o HTML do React para arquivos inexistentes.
if (preg_match('/\.[a-zA-Z0-9]+$/', $uri)) {
    http_response_code(404);
    echo 'Arquivo não encontrado.';
    return true;
}

// Fallback para as rotas do React.
if (is_file($root . '/index.html')) {
    readfile($root . '/index.html');
    return true;
}

http_response_code(404);
echo 'Página não encontrada.';
