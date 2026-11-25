<?php

class Avaliacao {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /** Salva a nota do usuário (uma por repositório) e o comentário correspondente. */
    public function criarAvaliacao(
        int $idUsuario,
        int $idRepositorio,
        string $texto,
        ?float $nota,
        ?int $idComentarioResposta = null
    ): bool {
        $dataAtual = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');

        try {
            $this->pdo->beginTransaction();

            if ($idComentarioResposta !== null) {
                $stmtResposta = $this->pdo->prepare(
                    'SELECT 1 FROM comentario WHERE id_comentario = ? AND id_repositorio = ?'
                );
                $stmtResposta->execute([$idComentarioResposta, $idRepositorio]);
                if (!$stmtResposta->fetchColumn()) {
                    throw new InvalidArgumentException('O comentário respondido não pertence a este repositório.');
                }
                if ($nota !== null) {
                    throw new InvalidArgumentException('Respostas não podem alterar a nota do repositório.');
                }
            }

            if ($nota !== null && $idComentarioResposta === null) {
                $sqlEstrela = '
                    INSERT INTO estrela (nota, data_estrela, id_usuario, id_repositorio)
                    VALUES (:nota, :data_estrela, :id_usuario, :id_repositorio)
                    ON CONFLICT (id_usuario, id_repositorio)
                    DO UPDATE SET nota = EXCLUDED.nota, data_estrela = EXCLUDED.data_estrela
                ';
                $stmtEstrela = $this->pdo->prepare($sqlEstrela);
                $stmtEstrela->execute([
                    ':nota' => $nota,
                    ':data_estrela' => $dataAtual,
                    ':id_usuario' => $idUsuario,
                    ':id_repositorio' => $idRepositorio
                ]);
            }

            $sqlComentario = '
                INSERT INTO comentario (
                    texto, like_opcao, dislike_opcao, data_comentario,
                    id_usuario, id_repositorio, id_comentario_resposta
                ) VALUES (:texto, 0, 0, :data_comentario, :id_usuario, :id_repositorio, :id_comentario_resposta)
            ';
            $stmtComentario = $this->pdo->prepare($sqlComentario);
            $stmtComentario->execute([
                ':texto' => $texto,
                ':data_comentario' => $dataAtual,
                ':id_usuario' => $idUsuario,
                ':id_repositorio' => $idRepositorio,
                ':id_comentario_resposta' => $idComentarioResposta
            ]);

            $this->pdo->commit();
            return true;
        } catch (InvalidArgumentException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('Erro ao salvar avaliação: ' . $e->getMessage());
            return false;
        }
    }

    /** Um comentário de maior nota e, em caso de empate, mais curtido para um repositório. */
    public function obterDestaque(int $idRepositorio): ?array {
        $sql = '
            SELECT c.id_comentario, c.texto,
                   COALESCE(c.like_opcao, 0) AS likes,
                   COALESCE(c.dislike_opcao, 0) AS dislikes,
                   c.data_comentario, u.id_usuario, u.nome, u.sobrenome,
                   u.caminho_foto_perfil, e.nota,
                   r.id_repositorio, r.titulo AS titulo_repositorio
            FROM comentario c
            INNER JOIN usuario u ON c.id_usuario = u.id_usuario
            INNER JOIN estrela e ON e.id_usuario = c.id_usuario
                                 AND e.id_repositorio = c.id_repositorio
            INNER JOIN repositorio r ON r.id_repositorio = c.id_repositorio
            WHERE c.id_repositorio = :id_repositorio
              AND c.id_comentario_resposta IS NULL
            ORDER BY e.nota DESC, COALESCE(c.like_opcao, 0) DESC, c.data_comentario DESC
            LIMIT 1
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_repositorio' => $idRepositorio]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Os dois comentários mais bem avaliados e mais curtidos de todo o site. */
    public function obterDestaques(): array {
        $sql = '
            SELECT c.id_comentario, c.texto,
                   COALESCE(c.like_opcao, 0) AS likes,
                   COALESCE(c.dislike_opcao, 0) AS dislikes,
                   c.data_comentario, u.id_usuario, u.nome, u.sobrenome,
                   u.caminho_foto_perfil, e.nota,
                   r.id_repositorio, r.titulo AS titulo_repositorio
            FROM comentario c
            INNER JOIN usuario u ON c.id_usuario = u.id_usuario
            INNER JOIN estrela e ON e.id_usuario = c.id_usuario
                                 AND e.id_repositorio = c.id_repositorio
            INNER JOIN repositorio r ON r.id_repositorio = c.id_repositorio
            WHERE c.id_comentario_resposta IS NULL
            ORDER BY e.nota DESC, COALESCE(c.like_opcao, 0) DESC, c.data_comentario DESC
            LIMIT 2
        ';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lista comentários e a média das estrelas do repositório. */
    public function listarPorRepositorio(int $idRepositorio, ?int $idUsuario = null): array {
        $sqlMedia = 'SELECT COALESCE(AVG(nota), 0) AS media, COUNT(*) AS total_votos
                     FROM estrela WHERE id_repositorio = :id';
        $stmtMedia = $this->pdo->prepare($sqlMedia);
        $stmtMedia->execute([':id' => $idRepositorio]);
        $resumo = $stmtMedia->fetch(PDO::FETCH_ASSOC) ?: ['media' => 0, 'total_votos' => 0];

        $minhaNota = null;
        if ($idUsuario !== null) {
            $stmtMinhaNota = $this->pdo->prepare(
                'SELECT nota FROM estrela WHERE id_repositorio = ? AND id_usuario = ?'
            );
            $stmtMinhaNota->execute([$idRepositorio, $idUsuario]);
            $valorNota = $stmtMinhaNota->fetchColumn();
            $minhaNota = $valorNota === false ? null : (float)$valorNota;
        }

        $sqlLista = '
            SELECT c.id_comentario, c.texto,
                   COALESCE(c.like_opcao, 0) AS likes,
                   COALESCE(c.dislike_opcao, 0) AS dislikes,
                   c.data_comentario, c.id_comentario_resposta,
                   u.id_usuario, u.nome, u.sobrenome, u.caminho_foto_perfil,
                   e.nota, rc.tipo AS minha_reacao
            FROM comentario c
            INNER JOIN usuario u ON c.id_usuario = u.id_usuario
            LEFT JOIN estrela e ON e.id_usuario = c.id_usuario
                                AND e.id_repositorio = c.id_repositorio
            LEFT JOIN reacao_comentario rc ON rc.id_comentario = c.id_comentario
                                          AND rc.id_usuario = :id_usuario
            WHERE c.id_repositorio = :id_repositorio
            ORDER BY c.data_comentario DESC
        ';
        $stmtLista = $this->pdo->prepare($sqlLista);
        $stmtLista->bindValue(':id_usuario', $idUsuario, $idUsuario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmtLista->bindValue(':id_repositorio', $idRepositorio, PDO::PARAM_INT);
        $stmtLista->execute();

        return [
            'media_estrelas' => round((float)$resumo['media'], 1),
            'total_avaliacoes' => (int)$resumo['total_votos'],
            'minha_nota' => $minhaNota,
            'comentarios' => $stmtLista->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    /** Um usuário pode manter apenas uma reação por comentário; clicar novamente remove a reação. */
    public function reagirComentario(int $idComentario, int $idUsuario, string $tipo): bool {
        if (!in_array($tipo, ['like', 'dislike'], true)) return false;

        try {
            $this->pdo->beginTransaction();

            $stmtAtual = $this->pdo->prepare(
                'SELECT tipo FROM reacao_comentario WHERE id_comentario = ? AND id_usuario = ? FOR UPDATE'
            );
            $stmtAtual->execute([$idComentario, $idUsuario]);
            $reacaoAtual = $stmtAtual->fetchColumn();

            if ($reacaoAtual === $tipo) {
                $stmt = $this->pdo->prepare(
                    'DELETE FROM reacao_comentario WHERE id_comentario = ? AND id_usuario = ?'
                );
                $stmt->execute([$idComentario, $idUsuario]);
            } else {
                $stmt = $this->pdo->prepare('
                    INSERT INTO reacao_comentario (id_comentario, id_usuario, tipo)
                    VALUES (?, ?, ?)
                    ON CONFLICT (id_comentario, id_usuario)
                    DO UPDATE SET tipo = EXCLUDED.tipo
                ');
                $stmt->execute([$idComentario, $idUsuario, $tipo]);
            }

            $stmtContagem = $this->pdo->prepare('
                SELECT
                    (SELECT COALESCE(likes_anteriores, 0) FROM comentario WHERE id_comentario = ?)
                        + COUNT(*) FILTER (WHERE tipo = \'like\') AS likes,
                    (SELECT COALESCE(dislikes_anteriores, 0) FROM comentario WHERE id_comentario = ?)
                        + COUNT(*) FILTER (WHERE tipo = \'dislike\') AS dislikes
                FROM reacao_comentario WHERE id_comentario = ?
            ');
            $stmtContagem->execute([$idComentario, $idComentario, $idComentario]);
            $contagem = $stmtContagem->fetch(PDO::FETCH_ASSOC);

            $stmtAtualiza = $this->pdo->prepare(
                'UPDATE comentario SET like_opcao = ?, dislike_opcao = ? WHERE id_comentario = ?'
            );
            $stmtAtualiza->execute([(int)$contagem['likes'], (int)$contagem['dislikes'], $idComentario]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('Erro ao registrar reação: ' . $e->getMessage());
            return false;
        }
    }

    public function excluirComentario(int $idComentario, int $idUsuario, bool $ehAdmin): bool {
        try {
            $stmtBusca = $this->pdo->prepare('SELECT id_usuario FROM comentario WHERE id_comentario = ?');
            $stmtBusca->execute([$idComentario]);
            $autor = $stmtBusca->fetchColumn();
            if ($autor === false || (!$ehAdmin && (int)$autor !== $idUsuario)) return false;

            $this->pdo->beginTransaction();
            // Remove respostas primeiro para manter compatibilidade com bancos sem ON DELETE CASCADE.
            $stmtRespostas = $this->pdo->prepare('DELETE FROM comentario WHERE id_comentario_resposta = ?');
            $stmtRespostas->execute([$idComentario]);
            $stmt = $this->pdo->prepare('DELETE FROM comentario WHERE id_comentario = ?');
            $stmt->execute([$idComentario]);
            $this->pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('Erro ao excluir comentário: ' . $e->getMessage());
            return false;
        }
    }
}
