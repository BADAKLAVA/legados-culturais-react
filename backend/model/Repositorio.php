<?php

class Repositorio {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function criarRepositorio($titulo, $descricao, $tipo, $caminho, $nomeArquivo, $dataRepositorio) {
        try {
            $sql = "INSERT INTO repositorio (titulo, descricao, tipo, caminho, nome_arquivo, data_repositorio)
                    VALUES (?, ?, ?, ?, ?, ?) RETURNING id_repositorio";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$titulo, $descricao, $tipo, $caminho, $nomeArquivo, $dataRepositorio]);
            return $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log("Erro ao criar repositório: " . $e->getMessage());
            return false;
        }
    }

    public function exibirRepositorio() {
        try {
            $sql = "SELECT
                        r.id_repositorio AS id,
                        r.titulo AS title,
                        r.descricao AS description,
                        r.tipo AS \"fileType\",
                        r.nome_arquivo AS \"fileName\",
                        r.data_repositorio AS \"date\",
                        COALESCE(JSON_AGG(t.nome) FILTER (WHERE t.nome IS NOT NULL), '[]'::json) AS tags
                    FROM repositorio r
                    LEFT JOIN detalhe_tag dt ON r.id_repositorio = dt.id_repositorio
                    LEFT JOIN tag t ON dt.id_tag = t.id_tag
                    GROUP BY r.id_repositorio
                    ORDER BY r.id_repositorio ASC";
            $stmt = $this->pdo->query($sql);
            $repositorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($repositorios as &$repo) {
                $repo['tags'] = json_decode($repo['tags'] ?? '[]', true) ?: [];
                $repo['type'] = $this->categoriaArquivo($repo['fileType'] ?? '');
                $repo['date'] = $repo['date'] ? date('d/m/Y', strtotime($repo['date'])) : '';
            }
            unset($repo);
            return $repositorios;
        } catch (Throwable $e) {
            error_log("Erro ao listar repositórios: " . $e->getMessage());
            return null;
        }
    }

    public function obterRepositorio($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_repositorio AS id, titulo AS title, descricao AS description,
                                                tipo AS \"fileType\", caminho AS \"filePath\", nome_arquivo AS \"fileName\",
                                                data_repositorio AS \"date\"
                                         FROM repositorio WHERE id_repositorio = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log("Erro ao obter repositório: " . $e->getMessage());
            return null;
        }
    }

    public function atualizarRepositorio($id, $titulo, $descricao, $tipo, $caminho, $nomeArquivo) {
        try {
            $stmt = $this->pdo->prepare("UPDATE repositorio
                                         SET titulo = ?, descricao = ?, tipo = ?, caminho = ?, nome_arquivo = ?
                                         WHERE id_repositorio = ?");
            return $stmt->execute([$titulo, $descricao, $tipo, $caminho, $nomeArquivo, $id]);
        } catch (Throwable $e) {
            error_log("Erro ao atualizar repositório: " . $e->getMessage());
            return false;
        }
    }

    public function excluirRepositorio($id) {
        try {
            // Remove referências dependentes antes do repositório.
            $this->pdo->prepare("UPDATE comentario SET id_comentario_resposta = NULL
                                 WHERE id_comentario_resposta IN
                                 (SELECT id_comentario FROM comentario WHERE id_repositorio = ?)")->execute([$id]);
            foreach (["historico", "estrela", "detalhe_tag", "comentario"] as $tabela) {
                $this->pdo->prepare("DELETE FROM {$tabela} WHERE id_repositorio = ?")->execute([$id]);
            }
            $stmt = $this->pdo->prepare("DELETE FROM repositorio WHERE id_repositorio = ?");
            return $stmt->execute([$id]);
        } catch (Throwable $e) {
            error_log("Erro ao excluir repositório: " . $e->getMessage());
            return false;
        }
    }

    public function registrarDownload($idUsuario, $idRepositorio) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO historico (data_historico, id_usuario, id_repositorio)
                                         VALUES (CURRENT_TIMESTAMP, ?, ?)");
            return $stmt->execute([$idUsuario, $idRepositorio]);
        } catch (Throwable $e) {
            error_log("Erro ao registrar download: " . $e->getMessage());
            return false;
        }
    }

    private function categoriaArquivo($extensao) {
        $extensao = strtolower((string)$extensao);
        if ($extensao === '.txt') return 'TXT';
        if (in_array($extensao, ['.png', '.jpg', '.jpeg', '.gif', '.webp'], true)) return 'Imagem';
        if (in_array($extensao, ['.pdf', '.doc', '.docx', '.odt'], true)) return 'Documento';
        return 'Arquivo';
    }
}
