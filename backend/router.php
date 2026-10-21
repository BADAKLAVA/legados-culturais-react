
<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Deixar as rotas e os arquivos PHP do backend
// serem processados normalmente pelo servidor.
if (str_starts_with($uri, '/backend/')) {
    return false;
}

// A raiz pública contém o index.html e os arquivos
// compilados do React.
$arquivo = realpath('/app' . $uri);

if (
    $arquivo !== false &&
    str_starts_with($arquivo, '/app/') &&
    is_file($arquivo)
) {
    return false;
}

// Para rotas do React que não correspondem a arquivos,
// entregar a página principal.
if (is_file('/app/index.html')) {
    readfile('/app/index.html');
    return true;
}

http_response_code(404);
echo 'Página não encontrada.';
