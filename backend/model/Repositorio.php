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
        error_log("Exibir repositorios no model");
        $repositorios = null;
        try {
            $sql = "
            SELECT
                r.id_repositorio AS id,
                r.titulo AS title,
                r.descricao AS \"description\",
                r.tipo AS \"type\",
                r.data_repositorio AS \"date\",
                COALESCE(
                    JSON_AGG(t.nome) FILTER (WHERE t.nome IS NOT NULL),
                    '[]'::json
                ) AS \"tags\"
            FROM repositorio AS r
            LEFT JOIN detalhe_tag AS dt
                ON r.id_repositorio = dt.id_repositorio
            LEFT JOIN tag AS t
                ON dt.id_tag = t.id_tag
            GROUP BY r.id_repositorio
            ORDER BY r.id_repositorio ASC
        ";

        $stmt = $this->pdo->query($sql);
        $repositorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $repositorios;

        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($repositorios, true));
            return null;
        }
    } 
}