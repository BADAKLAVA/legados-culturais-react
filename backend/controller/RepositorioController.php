<?php

class RepositorioController {
    private Repositorio $repositorio;
    private Tag $tagObj;
    private PDO $pdo;

    private const TIPOS_PERMITIDOS = ['.txt', '.pdf', '.docx', '.doc', '.odt', '.png', '.jpg', '.jpeg', '.gif', '.webp'];
    private const TAMANHO_MAXIMO = 25 * 1024 * 1024;

    public function __construct(Repositorio $repositorio, Tag $tagObj, PDO $pdo) {
        $this->repositorio = $repositorio;
        $this->tagObj = $tagObj;
        $this->pdo = $pdo;
    }

    private function dadosRecebidos(): array {
        if (!empty($_POST)) return $_POST;
        $json = json_decode(file_get_contents('php://input'), true);
        return is_array($json) ? $json : [];
    }

    private function validarArquivo(array $arquivo, bool $obrigatorio): ?array {
        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($obrigatorio) throw new InvalidArgumentException('Selecione um arquivo para o repositório.');
            return null;
        }
        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Não foi possível receber o arquivo enviado.');
        }
        if (($arquivo['size'] ?? 0) <= 0 || $arquivo['size'] > self::TAMANHO_MAXIMO) {
            throw new InvalidArgumentException('O arquivo deve ter até 25 MB.');
        }

        $nome = basename((string)($arquivo['name'] ?? ''));
        $extensao = '.' . strtolower(pathinfo($nome, PATHINFO_EXTENSION));
        if ($nome === '' || !in_array($extensao, self::TIPOS_PERMITIDOS, true)) {
            throw new InvalidArgumentException('Tipo de arquivo não permitido.');
        }
        if (!is_uploaded_file($arquivo['tmp_name'] ?? '')) {
            throw new InvalidArgumentException('O arquivo enviado é inválido.');
        }
        if (strlen($nome) > 255) throw new InvalidArgumentException('O nome do arquivo deve ter até 255 caracteres.');
        return ['nome' => $nome, 'tipo' => $extensao, 'tmp' => $arquivo['tmp_name']];
    }

    private function validarTags($tags): array {
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }
        if (!is_array($tags)) throw new InvalidArgumentException('As tags enviadas são inválidas.');

        $resultado = [];
        foreach ($tags as $tag) {
            if (!is_string($tag)) throw new InvalidArgumentException('Uma das tags é inválida.');
            $tag = trim($tag);
            if ($tag === '') continue;
            $tag = str_starts_with($tag, '#') ? $tag : '#' . $tag;
            if (strlen($tag) > 20) throw new InvalidArgumentException('Cada tag pode ter no máximo 20 caracteres.');
            $resultado[] = $tag;
        }
        return array_values(array_unique($resultado));
    }

    private function salvarTags(int $idRepositorio, array $tags): void {
        foreach ($tags as $tag) {
            if (!$this->tagObj->criarTag($tag)) throw new RuntimeException('Não foi possível salvar uma tag.');
            $existente = $this->tagObj->consultarTag($tag);
            if (!$existente || !$this->tagObj->relacionarRepositorio($existente['id_tag'], $idRepositorio)) {
                throw new RuntimeException('Não foi possível associar uma tag ao repositório.');
            }
        }
    }

    private function pastaUploads(): string {
        $pasta = dirname(__DIR__) . '/uploads/repositorios';
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
            throw new RuntimeException('Não foi possível preparar a pasta de arquivos.');
        }
        return $pasta;
    }

    public function criar(): void {
        $dados = $this->dadosRecebidos();
        $arquivoSalvo = null;

        try {
            $titulo = trim((string)($dados['title'] ?? ''));
            if ($titulo === '') throw new InvalidArgumentException('O título não pode estar vazio.');
            $descricao = trim((string)($dados['description'] ?? ''));
            $descricao = $descricao === '' ? null : $descricao;
            $tags = $this->validarTags($dados['tags'] ?? []);
            $arquivo = $this->validarArquivo($_FILES['arquivo'] ?? [], true);
            $nomeEnviado = trim((string)($dados['fileName'] ?? ''));
            $tipoEnviado = strtolower(trim((string)($dados['fileType'] ?? '')));
            if (($nomeEnviado !== '' && $nomeEnviado !== $arquivo['nome']) || ($tipoEnviado !== '' && $tipoEnviado !== $arquivo['tipo'])) {
                throw new InvalidArgumentException('Os metadados do arquivo não correspondem ao arquivo selecionado.');
            }
            $pasta = $this->pastaUploads();
            $nomeInterno = bin2hex(random_bytes(16)) . $arquivo['tipo'];
            $caminhoRelativo = 'uploads/repositorios/' . $nomeInterno;
            $data = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');

            $this->pdo->beginTransaction();
            $id = $this->repositorio->criarRepositorio(
                $titulo, $descricao, $arquivo['tipo'], $caminhoRelativo, $arquivo['nome'], $data
            );
            if (!$id) throw new RuntimeException('Não foi possível criar o repositório no banco de dados.');

            $arquivoSalvo = $pasta . '/' . $nomeInterno;
            if (!move_uploaded_file($arquivo['tmp'], $arquivoSalvo)) {
                throw new RuntimeException('Não foi possível armazenar o arquivo enviado.');
            }
            $this->salvarTags((int)$id, $tags);
            $this->pdo->commit();

            echo json_encode(['sucesso' => true, 'id' => (int)$id, 'mensagem' => 'Repositório criado com sucesso.']);
        } catch (InvalidArgumentException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if ($arquivoSalvo && is_file($arquivoSalvo)) unlink($arquivoSalvo);
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if ($arquivoSalvo && is_file($arquivoSalvo)) unlink($arquivoSalvo);
            error_log('Erro ao criar repositório: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao criar o repositório.']);
        }
    }

    public function editar(int $id): void {
        $dados = $this->dadosRecebidos();
        $novoArquivoSalvo = null;

        try {
            $existente = $this->repositorio->obterRepositorio($id);
            if (!$existente) {
                http_response_code(404);
                echo json_encode(['sucesso' => false, 'erro' => 'Repositório não encontrado.']);
                return;
            }
            $titulo = trim((string)($dados['title'] ?? ''));
            if ($titulo === '') throw new InvalidArgumentException('O título não pode estar vazio.');
            $descricao = trim((string)($dados['description'] ?? ''));
            $descricao = $descricao === '' ? null : $descricao;
            $tags = $this->validarTags($dados['tags'] ?? []);
            $arquivo = $this->validarArquivo($_FILES['arquivo'] ?? [], false);
            if ($arquivo) {
                $nomeEnviado = trim((string)($dados['fileName'] ?? ''));
                $tipoEnviado = strtolower(trim((string)($dados['fileType'] ?? '')));
                if (($nomeEnviado !== '' && $nomeEnviado !== $arquivo['nome']) || ($tipoEnviado !== '' && $tipoEnviado !== $arquivo['tipo'])) {
                    throw new InvalidArgumentException('Os metadados do arquivo não correspondem ao arquivo selecionado.');
                }
            }

            $tipo = $existente['fileType'];
            $caminho = $existente['filePath'];
            $nomeArquivo = $existente['fileName'];
            if ($arquivo) {
                $pasta = $this->pastaUploads();
                $nomeInterno = bin2hex(random_bytes(16)) . $arquivo['tipo'];
                $caminho = 'uploads/repositorios/' . $nomeInterno;
                $tipo = $arquivo['tipo'];
                $nomeArquivo = $arquivo['nome'];
                $novoArquivoSalvo = $pasta . '/' . $nomeInterno;
                if (!move_uploaded_file($arquivo['tmp'], $novoArquivoSalvo)) {
                    throw new RuntimeException('Não foi possível armazenar o novo arquivo.');
                }
            }

            $this->pdo->beginTransaction();
            if (!$this->repositorio->atualizarRepositorio($id, $titulo, $descricao, $tipo, $caminho, $nomeArquivo)) {
                throw new RuntimeException('Não foi possível atualizar o repositório.');
            }
            if (!$this->tagObj->removerRelacoesRepositorio($id)) throw new RuntimeException('Não foi possível atualizar as tags.');
            $this->salvarTags($id, $tags);
            $this->pdo->commit();

            if ($arquivo && !empty($existente['filePath'])) {
                $arquivoAntigo = dirname(__DIR__) . '/' . ltrim($existente['filePath'], '/');
                if (is_file($arquivoAntigo)) unlink($arquivoAntigo);
            }
            echo json_encode(['sucesso' => true, 'mensagem' => 'Repositório atualizado com sucesso.']);
        } catch (InvalidArgumentException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if ($novoArquivoSalvo && is_file($novoArquivoSalvo)) unlink($novoArquivoSalvo);
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if ($novoArquivoSalvo && is_file($novoArquivoSalvo)) unlink($novoArquivoSalvo);
            error_log('Erro ao editar repositório: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao editar o repositório.']);
        }
    }

    public function excluir(int $id): void {
        try {
            $existente = $this->repositorio->obterRepositorio($id);
            if (!$existente) {
                http_response_code(404);
                echo json_encode(['sucesso' => false, 'erro' => 'Repositório não encontrado.']);
                return;
            }
            $this->pdo->beginTransaction();
            if (!$this->repositorio->excluirRepositorio($id)) throw new RuntimeException('Falha ao excluir registro.');
            $this->pdo->commit();
            $arquivo = dirname(__DIR__) . '/' . ltrim($existente['filePath'] ?? '', '/');
            if ($arquivo && is_file($arquivo)) unlink($arquivo);
            echo json_encode(['sucesso' => true]);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('Erro ao excluir repositório: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível excluir o repositório.']);
        }
    }

    public function exibir(): void {
        $resposta = $this->repositorio->exibirRepositorio();
        if ($resposta === null) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao carregar os repositórios.']);
            return;
        }
        echo json_encode(['sucesso' => true, 'repositorios' => $resposta]);
    }

    public function exibirTags(): void {
        $tags = $this->tagObj->listarTags();
        if ($tags === null) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao carregar as tags.']);
            return;
        }
        echo json_encode(['sucesso' => true, 'tags' => $tags]);
    }

    public function criarTagGlobal(): void {
        try {
            $dados = $this->dadosRecebidos();
            $nome = trim((string)($dados['tag'] ?? ''));
            $nome = str_starts_with($nome, '#') ? $nome : '#' . $nome;
            if ($nome === '#' || strlen($nome) > 20) throw new InvalidArgumentException('A tag deve ter entre 1 e 19 caracteres, além do #.');
            if (!$this->tagObj->criarTag($nome)) throw new RuntimeException('Não foi possível salvar a tag.');
            echo json_encode(['sucesso' => true, 'tag' => $nome]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        } catch (Throwable $e) {
            error_log('Erro ao criar tag: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível criar a tag.']);
        }
    }
}
