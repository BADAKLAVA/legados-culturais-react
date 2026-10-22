
<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = '/app';

// Encaminha as requisições da API para o arquivo de rotas.
if ($uri === '/routes.php') {
    require $root . '/backend/routes/routes.php';
    return true;
}

// Serve arquivos estáticos existentes.
$file = realpath($root . $uri);

if (
    $file !== false &&
    str_starts_with($file, $root . '/') &&
    is_file($file)
) {
    return false;
}

// Não devolve o index.html para recursos inexistentes.
if (preg_match('/\.[a-zA-Z0-9]+$/', $uri)) {
    http_response_code(404);
    echo 'Arquivo não encontrado.';
    return true;
}

// Permite que o React controle as demais rotas.
if (is_file($root . '/index.html')) {
    readfile($root . '/index.html');
    return true;
}

http_response_code(404);
echo 'Página não encontrada.';