<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = '/app';

// Se o caminho corresponder a um arquivo existente,
// deixar o servidor PHP servi-lo diretamente.
$file = realpath($root . $uri);

if (
    $file !== false &&
    str_starts_with($file, $root . '/') &&
    is_file($file)
) {
    return false;
}

// Se o arquivo solicitado parecer um recurso estático
// (JS, CSS, imagens etc.) e não existir, retornar 404.
if (preg_match('/\.[a-zA-Z0-9]+$/', $uri)) {
    http_response_code(404);
    echo 'Arquivo não encontrado.';
    return true;
}

// As demais rotas podem ser tratadas pelo React.
if (is_file($root . '/index.html')) {
    readfile($root . '/index.html');
    return true;
}

http_response_code(404);
echo 'Página não encontrada.';
