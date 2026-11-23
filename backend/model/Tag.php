<?php

class Tag {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function criarTag($nome) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tag(nome) VALUES (?) ON CONFLICT (nome) DO NOTHING");
            $stmt->execute([$nome]);
            return true;
        } catch (Throwable $e) {
            error_log("Erro ao criar tag: " . $e->getMessage());
            return false;
        }
    }

    public function relacionarRepositorio($idTag, $idRepositorio) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO detalhe_tag(id_tag, id_repositorio) VALUES (?, ?)
                                         ON CONFLICT (id_repositorio, id_tag) DO NOTHING");
            return $stmt->execute([$idTag, $idRepositorio]);
        } catch (Throwable $e) {
            error_log("Erro ao relacionar tag: " . $e->getMessage());
            return false;
        }
    }

    // Mantém o nome antigo para compatibilidade com chamadas existentes.
    public function relacionarUsuario($idTag, $idRepositorio) {
        return $this->relacionarRepositorio($idTag, $idRepositorio);
    }

    public function consultarTag($nome) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_tag, nome FROM tag WHERE nome = ?");
            $stmt->execute([$nome]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (Throwable $e) {
            error_log("Erro ao consultar tag: " . $e->getMessage());
            return null;
        }
    }

    public function listarTags() {
        try {
            $stmt = $this->pdo->query("SELECT nome FROM tag ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            error_log("Erro ao listar tags: " . $e->getMessage());
            return null;
        }
    }

    public function removerRelacoesRepositorio($idRepositorio) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM detalhe_tag WHERE id_repositorio = ?");
            return $stmt->execute([$idRepositorio]);
        } catch (Throwable $e) {
            error_log("Erro ao remover relações de tags: " . $e->getMessage());
            return false;
        }
    }
}
