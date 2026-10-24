<?php 

class Usuario {
    private PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    //CRUD -> CREATE
    public function criarUsuario(
        $tipo,
        $nome,
        $sobrenome,
        $email,
        $senha,
        $telefone,
        $dataNascimento,
        $sexo,
        $genero,
        $estado,
        $cidade,
        $bairro,
        $logradouro,
        $numero,
        $complemento
    ) {
        $sql = "INSERT INTO usuario(tipo, nome, sobrenome, email, senha, telefone, data_nascimento, sexo, genero, estado, cidade, bairro, logradouro, numero, complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        error_log(print_r($stmt, true));

        $resultado = null;

        try {
            $resultado = $stmt->execute([
                $tipo,
                $nome,
                $sobrenome,
                $email,
                $senha,
                $telefone,
                $dataNascimento,
                $sexo,
                $genero,
                $estado,
                $cidade,
                $bairro,
                $logradouro,
                $numero,
                $complemento
            ]);

            return $resultado;

        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

    //public function getId() {}

    //CRUD -> READ
    //procurar coluna de um determinado usuario
    public function obterColunaUsuario($coluna, $idUsuario) {
        $resultado = null;
        try {
            $sql = "SELECT ? FROM usuario WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$coluna, $idUsuario]); 
            $resultado = $stmt->fetchColumn();
            return $resultado;
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

    //CRUD -> READ
    //procura uma coluna com determinado valor
    public function pesquisarColuna(string $coluna, $valor) {
        $resultado = null;
        try {
            $sql = "SELECT $coluna FROM usuario WHERE $coluna = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$valor]);
            $resultado = $stmt->fetchColumn();
            return $resultado;
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

    //CRUD -> READ
    //lista todos os dados havendo uma condição ou não
    public function listarDados($coluna = null, $valor = null) {
        $resultado = null;
        try {
            if($coluna !== null && $valor !== null) {
                $sql = "SELECT * FROM usuario WHERE $coluna = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$valor]);
            } else {
                $sql = "SELECT * FROM usuario";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
            }
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
    }

   //CRUD -> UPDATE
    public function atualizarColuna($idUsuario, $coluna, $valor) {
        $sql = "UPDATE usuario SET ? = ? WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coluna, $valor, $idUsuario]);
        
   }

}

?>
