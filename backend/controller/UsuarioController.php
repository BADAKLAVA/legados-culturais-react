<?php 
require_once "model/Usuario.php";

class UsuarioController {
    private Usuario $usuario;

    public function __construct($pdo) {
        $this->usuario = new Usuario($pdo);
    }

    public function cadastrar() {
        $dados = json_decode(file_get_contents("php://input"), true);
        $nome = $dados["nome"];
        $sobrenome = $dados["sobrenome"];
        $email = $dados["email"];
        $senha = $dados["senha"];
        $telefone = $dados["telefone"];
        $dataNascimento = $dados["dataNascimento"];
        $sexo = $dados["sexo"];
        $genero = $dados["genero"];
        $estado = $dados["estado"];
        $cidade = $dados["cidade"];
        $bairro = $dados["bairro"];
        $logradouro = $dados["logradouro"];
        $numero = $dados["numero"];
        $complemento = $dados["complemento"];

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