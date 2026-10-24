<?php 
require_once __DIR__ . "/../model/Usuario.php";

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

    /* 
    public function cadastrar() {
    header("Content-Type: application/json; charset=UTF-8");

    try {
        $dados = json_decode(file_get_contents("php://input"), true);

        if (!is_array($dados)) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Dados JSON inválidos."
            ]);
            return;
        }

        $nome = $dados["nome"] ?? null;
        $sobrenome = $dados["sobrenome"] ?? null;
        $email = $dados["email"] ?? null;
        $senhaOriginal = $dados["senha"] ?? null;

        if (!$nome || !$sobrenome || !$email || !$senhaOriginal) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Campos obrigatórios ausentes."
            ]);
            return;
        }

        $senha = password_hash($senhaOriginal, PASSWORD_DEFAULT);

        $resultado = $this->usuario->criarUsuario(
            $nome,
            $sobrenome,
            $email,
            $senha,
            $dados["telefone"] ?? null,
            $dados["dataNascimento"] ?? null,
            $dados["sexo"] ?? null,
            $dados["genero"] ?? null,
            $dados["estado"] ?? null,
            $dados["cidade"] ?? null,
            $dados["bairro"] ?? null,
            $dados["logradouro"] ?? null,
            $dados["numero"] ?? null,
            $dados["complemento"] ?? null
        );

        if (!$resultado) {
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "erro" => "O banco não confirmou a inserção."
            ]);
            return;
        }

        echo json_encode(["sucesso" => true]);

    } catch (Throwable $e) {
        error_log("Erro no cadastro: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            "sucesso" => false,
            "erro" => "Erro interno ao cadastrar usuário."
        ]);
    }
    }*/

    public function login() {
    
        error_log("Login iniciado");

        $dados = json_decode(file_get_contents("php://input"), true);

        error_log("JSON recebido: " . json_encode(
                array_keys($dados ?? [])
            ));

        $email = $dados["email"];
        $senha = $dados["senha"];

        $usuarioDados = $this->usuario->listarDados("email", $email);

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

        $_SESSION["id_usuario"] = $usuarioDados["id_usuario"];
        $_SESSION["tipo"] = $usuarioDados["tipo"];

        echo json_encode([
            "sucesso" => true
        ]);
    }
}
?>