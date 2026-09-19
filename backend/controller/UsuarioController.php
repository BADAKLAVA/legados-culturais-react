<?php 
require_once "model/Usuario.php";

class UsuarioController {
    private Usuario $usuario;

    public function __construct($pdo) {
        $this->usuario = new Usuario($pdo);
    }

    public function cadastrar() {
        $nome = $_POST["nome"];
        $sobrenome = $_POST["sobrenome"];
        $email = $_POST["email"];
        $senha = $_POST["senha"];
        $telefone = $_POST["telefone"];
        $dataNascimento = $_POST["dataNascimento"];
        $sexo = $_POST["sexo"];
        $genero = $_POST["genero"];
        $outroGenero = $_POST["outroGenero"];
        $estado = $_POST["estado"];
        $cidade = $_POST["cidade"];
        $bairro = $_POST["bairro"];
        $logradouro = $_POST["logradouro"];
        $numero = $_POST["numero"];
        $complemento = $_POST["complemento"];

        $resultado = $this->usuario->criarUsuario(
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
        );

        echo json_encode(["sucesso" => $resultado]);
    }

    public function login() {
        
    }
}

?>