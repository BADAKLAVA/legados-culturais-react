<?php

class AvaliacaoController {
    private Avaliacao $avaliacao;

    public function __construct(Avaliacao $avaliacao) {
        $this->avaliacao = $avaliacao;
    }

    private function dadosRecebidos(): array {
        $json = json_decode(file_get_contents('php://input'), true);
        return is_array($json) ? $json : [];
    }

    public function criar(int $idUsuario): void {
        try {
            $dados = $this->dadosRecebidos();
            $idRepositorio = filter_var($dados['id_repositorio'] ?? null, FILTER_VALIDATE_INT);
            $texto = trim((string)($dados['texto'] ?? ''));
            $notaRaw = $dados['nota'] ?? null;
            $idResposta = filter_var($dados['id_comentario_resposta'] ?? null, FILTER_VALIDATE_INT) ?: null;

            if (!$idRepositorio || $idRepositorio <= 0) {
                throw new InvalidArgumentException('Repositório inválido.');
            }
            if ($texto === '' || mb_strlen($texto) > 2000) {
                throw new InvalidArgumentException('O comentário deve ter entre 1 e 2000 caracteres.');
            }

            $nota = null;
            if ($notaRaw !== null && $notaRaw !== '') {
                $nota = (float)$notaRaw;
                if ($nota < 0.5 || $nota > 5.0) {
                    throw new InvalidArgumentException('A nota deve ser entre 0.5 e 5.0 estrelas.');
                }
            }
            if ($idResposta !== null && $nota !== null) {
                throw new InvalidArgumentException('Respostas não podem alterar a nota do repositório.');
            }

            if (!$this->avaliacao->criarAvaliacao($idUsuario, $idRepositorio, $texto, $nota, $idResposta)) {
                throw new RuntimeException('Não foi possível registrar sua avaliação.');
            }

            http_response_code(201);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Avaliação publicada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno ao salvar avaliação.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function exibirDestaque(int $idRepositorio): void {
        $destaque = $this->avaliacao->obterDestaque($idRepositorio);
        echo json_encode(['sucesso' => true, 'destaque' => $destaque], JSON_UNESCAPED_UNICODE);
    }

    public function exibirDestaques(): void {
        $destaques = $this->avaliacao->obterDestaques();
        echo json_encode(['sucesso' => true, 'destaques' => $destaques], JSON_UNESCAPED_UNICODE);
    }

    public function listar(int $idRepositorio, ?int $idUsuario = null): void {
        $dados = $this->avaliacao->listarPorRepositorio($idRepositorio, $idUsuario);
        echo json_encode(['sucesso' => true, 'data' => $dados], JSON_UNESCAPED_UNICODE);
    }

    public function reagir(int $idComentario, int $idUsuario): void {
        $dados = $this->dadosRecebidos();
        $tipo = $dados['tipo'] ?? '';
        if (!in_array($tipo, ['like', 'dislike'], true)) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Reação inválida.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $ok = $this->avaliacao->reagirComentario($idComentario, $idUsuario, $tipo);
        if (!$ok) http_response_code(400);
        echo json_encode(['sucesso' => $ok], JSON_UNESCAPED_UNICODE);
    }

    public function excluir(int $idComentario, int $idUsuario, bool $ehAdmin): void {
        $ok = $this->avaliacao->excluirComentario($idComentario, $idUsuario, $ehAdmin);
        if (!$ok) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível excluir o comentário.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['sucesso' => true], JSON_UNESCAPED_UNICODE);
    }
}
