<?php 

use DateTime;

class Usuario {
    private PDO $pdo;
    private int $idUsuario;
    private string $tipo;
    private string $nome;
    private string $sobrenome;
    private string $email;
    private string $senha;
    private string $telefone;
    private DateTime $dataNascimento; 
    private string $sexo;
    private string $genero;
    private string $estado;
    private string $cidade;
    private string $bairro;
    private string $logradouro;
    private string $numero;
    private string $complemento;
    private string $imagemPerfil;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    //CRUD -> CREATE
    public function criarUsuario(
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
        error_log("Chegou no Usuario model");

        $sql = "INSERT INTO usuario(nome, sobrenome, email, senha, telefone, data_nascimento, sexo, genero, estado, cidade, bairro, logradouro, numero, complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        error_log("PDO: " . get_class($this->pdo));
        error_log("SQL: " . $sql);

        $stmt = $this->pdo->prepare($sql);

        error_log("DEPOIS DO PREPARE");
        error_log(print_r($stmt, true));

        error_log("Nome: " . var_export($nome, true));
        error_log("Email: " . var_export($email, true));

        $resultado = $stmt->execute([
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
    
        error_log("DEPOIS DO EXECUTE");
        error_log("RESULTADO: " . var_export($resultado, true));

        return $resultado;
    }
    //CRUD -> READ
    public function listarInformacoes($id_usuario) {
        $sql = "SELECT * FROM usuario WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //CRUD -> READ
    public function buscarEmail($email) {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}

?>
