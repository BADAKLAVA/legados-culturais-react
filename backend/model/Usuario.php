<?php 

//require_once "Endereco.php";
//require_once "config/database.php";
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
        $sql = "INSERT INTO usuario(nome, sobrenome, email, senha, telefone, data_nascimento, sexo, genero, estado, cidade, bairro, logradouro, numero, complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
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
