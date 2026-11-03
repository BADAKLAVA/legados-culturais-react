<?php 
require_once __DIR__ . "/../model/Usuario.php";
require_once __DIR__ . "/../model/RecuperacaoSenha.php";

class UsuarioController {
    private Usuario $usuario;

    public function __construct(Usuario $usuario) {
        $this->usuario = $usuario;
    }

    public function cadastrar() {
        header("Content-Type: application/json; charset=utf-8");
        error_log("Iniciou o método cadastrar");

        $dados = json_decode(file_get_contents("php://input"), true);

        error_log("JSON recebido: " . json_encode(
            array_keys($dados ?? [])
        ));
        
        $tipo = $dados["tipoUsuario"];
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

        try {
            $emailExistente = $this->usuario->pesquisarColuna("email", $email);
            $telefoneExistente = $this->usuario->pesquisarColuna("telefone", $telefone);

            if($emailExistente === null || $telefoneExistente === null) {
                http_response_code(500);

                echo json_encode([
                    "sucesso" => false,
                    "erro" => "Erro. Tente novamente"
                ]);
                return;
            }
            if($emailExistente !== false) {
                echo json_encode([
                    "sucesso" => false,
                    "erro" => "Uma conta com este e-mail já existe"
                ]);
                return;
            }
            if($telefoneExistente !== false) {
                echo json_encode([
                    "sucesso" => false,
                    "erro" => "Uma conta com este telefone já existe"
                ]);
                return;
            }
            $resultado = $this->usuario->criarUsuario(
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
            );
            echo json_encode(["sucesso" => $resultado]);
        } catch (\Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            echo json_encode([
                "sucesso" => false,
                "erro" => "Erro ao cadastrar"
            ]);
        }
    }

    public function login() {
        header("Content-Type: application/json; charset=UTF-8");
        error_log("Login iniciado");

        $dados = json_decode(file_get_contents("php://input"), true);

        error_log("JSON recebido: " . json_encode(
                array_keys($dados ?? [])
            ));

        $email = $dados["email"];
        $senha = $dados["senha"];

        error_log("EMAIL: $email");
        error_log("SENHA: $senha");

        try {
            $usuarioDados = $this->usuario->listarDados("email", $email);
        } catch (\Throwable $e) {
            error_log("Erro ao pegar email");
            error_log("ERRO: " . $e->getMessage());
            echo json_encode([
                "sucesso" => false,
                "erro" => "Erro no login. Tente novamente"
            ]);
            return;
        }

        error_log($usuarioDados["email"]);
        error_log($usuarioDados["senha"]);

        if (!$usuarioDados["email"]) {

            http_response_code(401);

            echo json_encode([
                "sucesso" => false,
                "erro" => "Usuário inválido"
            ]);
            return;
        }
        if (!password_verify($senha, $usuarioDados["senha"])) {

            http_response_code(401);

            echo json_encode([
                "sucesso" => false,
                "erro" => "Senha errada"
            ]);
            return;
        }

        if ($this->sessionExiste() === true && $usuarioDados["id_usuario"] === $_SESSION["id_usuario"]) {
            error_log("Login já existe");
            echo json_encode([
                "sucesso" => true
            ]);
            return;
        }

        $_SESSION["id_usuario"] = $usuarioDados["id_usuario"];
        $_SESSION["tipo"] = $usuarioDados["tipo"];

        error_log($_SESSION["id_usuario"]);
        error_log($_SESSION["tipo"]);

        echo json_encode([
            "sucesso" => true
        ]);
    }

    public function sessionExiste() {
        if(isset($_SESSION["id_usuario"]) && isset($_SESSION["tipo"])) {
            return true;
        } else {
            return false;
        }
    }
}
?>