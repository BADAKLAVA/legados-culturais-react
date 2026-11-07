<?php

class Tag {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function criarTag($nome) {
        $resultado = null;
        try {
            $sql = "INSERT INTO tag(nome) VALUES (?)";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$nome]);
            return $resultado;
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

    public function relacionarUsuario($idTag, $idRepositorio) {
        $resultado = null;
        try {
            $sql = "INSERT INTO detalhe_tag(id_tag, id_repositorio) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$idTag, $idRepositorio]);
            return $resultado;
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

    public function consultarTag($nome) {
    $resultado = null;

    try {
        $sql = "
            SELECT * FROM tag
            WHERE nome = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado;

    } catch (Throwable $e) {
        error_log("ERRO: " . $e->getMessage());
        return null;
    }
}
}