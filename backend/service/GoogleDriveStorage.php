<?php

/**
 * Armazenamento privado dos arquivos do Legados Culturais no Google Drive.
 * Os IDs dos arquivos são gravados em repositorio.caminho como "gdrive:ID".
 */
class GoogleDriveStorage {
    private Google\Service\Drive $drive;
    private string $folderId;

    public function __construct() {
        if (!class_exists(Google\Client::class)) {
            throw new RuntimeException('A biblioteca Google API PHP Client não está instalada. Execute composer install/update.');
        }

        $clientId = self::env('GOOGLE_CLIENT_ID');
        $clientSecret = self::env('GOOGLE_CLIENT_SECRET');
        $refreshToken = self::env('GOOGLE_REFRESH_TOKEN');
        $folderId = self::env('GOOGLE_DRIVE_FOLDER_ID');

        if (!$clientId || !$clientSecret || !$refreshToken || !$folderId) {
            throw new RuntimeException('Configure GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REFRESH_TOKEN e GOOGLE_DRIVE_FOLDER_ID.');
        }

        $client = new Google\Client();
        $client->setApplicationName('Legados Culturais');
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes([Google\Service\Drive::DRIVE_FILE]);
        $client->setAccessType('offline');
        $client->setAccessToken(['refresh_token' => $refreshToken]);

        $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
        if (isset($token['error'])) {
            $mensagem = $token['error_description'] ?? $token['error'];
            throw new RuntimeException('Não foi possível renovar a autorização do Google Drive: ' . $mensagem);
        }
        if (empty($token['access_token'])) {
            throw new RuntimeException('O Google não retornou um token de acesso válido. Refaça a autorização OAuth.');
        }

        $client->setAccessToken($token);
        $this->drive = new Google\Service\Drive($client);
        $this->folderId = $folderId;
    }

    private static function env(string $name): ?string {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        if ($value === false || $value === null || trim((string)$value) === '') return null;
        return trim((string)$value);
    }

    /** Envia um arquivo binário e devolve o ID privado gerado pelo Drive. */
    public function upload(string $caminhoTemporario, string $nomeOriginal, string $mimeType): string {
        if (!is_file($caminhoTemporario) || !is_readable($caminhoTemporario)) {
            throw new RuntimeException('O arquivo temporário não está disponível para envio ao Google Drive.');
        }

        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $nomeInterno = bin2hex(random_bytes(16)) . ($extensao !== '' ? '.' . $extensao : '');
        $conteudo = file_get_contents($caminhoTemporario);
        if ($conteudo === false) throw new RuntimeException('Não foi possível ler o arquivo enviado.');

        $metadados = new Google\Service\Drive\DriveFile([
            'name' => $nomeInterno,
            'mimeType' => $mimeType,
            'parents' => [$this->folderId]
        ]);

        $resultado = $this->drive->files->create($metadados, [
            'data' => $conteudo,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        $id = (string)$resultado->getId();
        if ($id === '') throw new RuntimeException('O Google Drive não retornou o identificador do arquivo enviado.');
        return $id;
    }

    /** Recupera o conteúdo de um arquivo binário privado. */
    public function download(string $fileId): string {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $fileId)) {
            throw new InvalidArgumentException('Identificador de arquivo do Google Drive inválido.');
        }

        $response = $this->drive->files->get($fileId, ['alt' => 'media']);
        $conteudo = $response->getBody()->getContents();
        if ($conteudo === '') {
            // Arquivos vazios não são permitidos pelo upload, portanto um retorno vazio indica arquivo indisponível.
            throw new RuntimeException('O Google Drive retornou um arquivo vazio.');
        }
        return $conteudo;
    }

    /** Exclui um arquivo privado pelo ID. */
    public function delete(string $fileId): void {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $fileId)) {
            throw new InvalidArgumentException('Identificador de arquivo do Google Drive inválido.');
        }
        $this->drive->files->delete($fileId);
    }
}
