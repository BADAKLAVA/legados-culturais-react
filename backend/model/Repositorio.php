<?php 

class Repositorio {
    private PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    //CRUD -> CREATE
    public function criarRepositorio(
        $titulo,
        $descricao = null,
        $caminho,
        $dataRepositorio
    ) {
        error_log("Chegou para criar no model do repositorio");
        $resultado = null;
        try {
            $sql = "
                INSERT INTO repositorio(
                titulo, descricao, caminho, data_repositorio
                ) VALUES (?, ?, ?, ?)
            ";

            $stmt = $this->pdo->prepare($sql);
            error_log("Preparando para inserir");

            $resultado = $stmt->execute([
                $titulo, $descricao, $caminho, $dataRepositorio
            ]); 

            return $resultado;
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

    //CRUD -> READ
    //seleciona as informacoes necessarias para
    //que a tela de biblioteca funcione
    public function exibirRepositorio() {
        $repositorios = null;
        try {
            $sql = "SELECT "
        }
    } 
}