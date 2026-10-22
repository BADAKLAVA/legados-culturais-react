<?php

$publicDir = realpath(__DIR__ . '/../public');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Encaminhar login e cadastro para as rotas do backend.
if ($path === '/routes.php') {
    require __DIR__ . '/routes/routes.php';
    exit;
}

// 2. Verificar se a pasta pública existe.
if ($publicDir === false) {
    http_response_code(500);
    echo 'Diretório público não encontrado.';
    exit;
}

// 3. Entregar arquivos existentes do frontend.
$file = realpath($publicDir . $path);

if (
    $path !== '/' &&
    $file !== false &&
    str_starts_with($file, $publicDir . DIRECTORY_SEPARATOR) &&
    is_file($file)
) {
    return false;
}

// 4. Entregar o React para a página inicial e outras URLs do frontend.
$index = $publicDir . '/index.html';

if (is_file($index)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($index);
    exit;
}

http_response_code(500);
echo 'Arquivo index.html não encontrado.';