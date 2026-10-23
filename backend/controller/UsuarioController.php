<?php 
require_once __DIR__ . "/../model/Usuario.php";

class UsuarioController {
    private Usuario $usuario;

    public function __construct($pdo) {
        $this->usuario = new Usuario($pdo);
    }

    
    public function cadastrar() {
        error_log("Iniciou o método cadastrar");

        $dados = json_decode(file_get_contents("php://input"), true);

        error_log("JSON recebido: " . json_encode(
            array_keys($dados ?? [])
        ));
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

    $usuarioDados = $this->usuario->buscarEmail($email);

    if (!$usuarioDados) {

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