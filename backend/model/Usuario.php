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
        $resultado = null;
        try {
            $sql = "INSERT INTO usuario(tipo, nome, sobrenome, email, senha, telefone, data_nascimento, sexo, genero, estado, cidade, bairro, logradouro, numero, complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);

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

    //CRUD -> READ
    //procurar coluna de um determinado usuario
    public function obterColunaUsuario($coluna, $idUsuario) {
        $resultado = null;
        try {
            $sql = "SELECT $coluna FROM usuario WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idUsuario]); 
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
        error_log("Atualizar coluna");
        $resultado = null;
        try {
            $sql = "UPDATE usuario SET $coluna = ? WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$valor, $idUsuario]);
            return $resultado;
        } catch(\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));
            return null;
        }
   }


    public function obterPerfil($idUsuario) {
        try {
            $sql = "SELECT id_usuario, nome, sobrenome, email, telefone,
                           TO_CHAR(data_nascimento, 'YYYY-MM-DD') AS data_nascimento,
                           sexo, genero, estado, cidade, bairro, logradouro, numero, complemento
                    FROM usuario WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log("Erro ao obter perfil: " . $e->getMessage());
            return null;
        }
    }

    public function atualizarPerfil($idUsuario, array $dados) {
        $camposPermitidos = [
            'nome', 'sobrenome', 'email', 'telefone', 'data_nascimento', 'sexo',
            'genero', 'estado', 'cidade', 'bairro', 'logradouro', 'numero', 'complemento'
        ];
        $campos = [];
        $valores = [];
        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[] = $campo . ' = ?';
                $valores[] = $dados[$campo] === '' ? null : $dados[$campo];
            }
        }
        if (!$campos) return false;

        try {
            $valores[] = $idUsuario;
            $sql = 'UPDATE usuario SET ' . implode(', ', $campos) . ' WHERE id_usuario = ?';
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($valores);
        } catch (Throwable $e) {
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            return false;
        }
    }

    public function listarDownloads($idUsuario) {
        try {
            $sql = "SELECT h.data_historico, r.id_repositorio AS id, r.titulo AS titulo,
                           COALESCE(r.descricao, '') AS descricao
                    FROM historico h
                    INNER JOIN repositorio r ON r.id_repositorio = h.id_repositorio
                    WHERE h.id_usuario = ?
                    ORDER BY h.data_historico DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Erro ao listar downloads: " . $e->getMessage());
            return null;
        }
    }

}

?>
