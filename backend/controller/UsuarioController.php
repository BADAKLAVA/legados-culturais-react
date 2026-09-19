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
        $senha = password_hash($dados["senha"], PASSWORD_DEFAULT);
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
        $dados = json_decode(file_get_contents("php://input"), true);
        $email = $dados["email"];
        $senha = $dados["senha"];

        $usuarioDados = $this->usuario->buscarEmail($email);

        if(!$usuarioDados) {
            echo json_encode([
                "sucesso" => false,
                "erro" => "Usuário inválido"
            ]);
            return;
        }

        if(!password_verify($senha, $usuarioDados["senha"])) {
            echo json_encode([
                "sucesso" => false,
                "erro" => "Senha errada"
            ]);
            return;
        }

        $_SESSION["id_usuario"] = $usuarioDados["id_usuario"];
        $_SESSION["tipo"] = $usuarioDados["tipo"];

        echo json_encode(["sucesso" => true]);
    }
}

?>