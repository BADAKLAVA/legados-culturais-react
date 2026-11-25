<?php 
require_once __DIR__ . "/../model/Usuario.php";
require_once __DIR__ . "/../model/RecuperacaoSenha.php";
require_once __DIR__ . "/../model/Analytics.php";

class UsuarioController {
    private Usuario $usuario;
    private string $tipoCadastro;
    private ?Analytics $analytics;

    public function __construct(Usuario $usuario, string $tipoCadastro = 'us', ?Analytics $analytics = null) {
        $this->usuario = $usuario;
        $this->tipoCadastro = $tipoCadastro === 'ad' ? 'ad' : 'us';
        $this->analytics = $analytics;
    }

    public function cadastrar() {
        header("Content-Type: application/json; charset=utf-8");
        error_log("Iniciou o método cadastrar");

        $dados = json_decode(file_get_contents("php://input"), true);

        error_log("JSON recebido: " . json_encode(
            array_keys($dados ?? [])
        ));
        
        // O tipo da conta é definido pela rota, nunca pelo JSON enviado pelo cliente.
        $tipo = $this->tipoCadastro;
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

        $email = trim((string)($dados["email"] ?? ""));
        $senha = (string)($dados["senha"] ?? "");
        if ($email === "" || $senha === "") {
            http_response_code(400);
            echo json_encode(["sucesso" => false, "erro" => "Informe e-mail e senha."]);
            return;
        }

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

        if (!$usuarioDados || empty($usuarioDados["email"])) {

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

        if ($this->sessionExiste() === true && (int)$usuarioDados["id_usuario"] === (int)$_SESSION["id_usuario"]) {
            error_log("Login já existe");
            echo json_encode([
                "sucesso" => true
            ]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION["id_usuario"] = (int)$usuarioDados["id_usuario"];
        $tipoBanco = strtolower(trim((string)$usuarioDados["tipo"]));
        $_SESSION["tipo"] = in_array($tipoBanco, ["ad", "adm", "administrador"], true) ? "adm" : "us";

        error_log($_SESSION["id_usuario"]);
        error_log($_SESSION["tipo"]);

        $this->analytics?->registrarEvento(
            'login',
            '/login',
            (int)$usuarioDados['id_usuario'],
            Analytics::hashSessaoAtual()
        );

        echo json_encode([
            "sucesso" => true
        ]);
    }

    public function sessionExiste() {
        if(isset($_SESSION["id_usuario"]) && !empty($_SESSION["id_usuario"]) && isset($_SESSION["tipo"]) && !empty($_SESSION["tipo"])) {
            return true;
        } else {
            return false;
        }
    }
}
?>