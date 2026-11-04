<?php
require_once __DIR__ . "/../model/Usuario.php";
require_once __DIR__ . "/../model/RecuperacaoSenha.php";
require_once __DIR__ . "/../service/EmailService.php";

class RecuperacaoSenhaController {
    private Usuario $usuario;
    private RecuperacaoSenha $recuperacaoSenha;
    private EmailService $emailService;
    private PDO $pdo;

    public function __construct(Usuario $usuario, RecuperacaoSenha $recuperacaoSenha, EmailService $emailService, PDO $pdo) {
        $this->usuario = $usuario;
        $this->recuperacaoSenha = $recuperacaoSenha;
        $this->emailService = $emailService;
        $this->pdo = $pdo;
    }

    public function solicitarRecuperacao() {
        header("Content-Type: application/json; charset=UTF-8");
        $dados = json_decode(file_get_contents("php://input"), true);
        
        if (!is_array($dados)) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Informe um e-mail válido."
            ]);
            return;
        }

        $email = $dados["email"] ?? null;

        if (!is_string($email) || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Informe um e-mail válido."
            ]);
            return;
        }

        $email = trim($email);

        try {
            $usuarioDados = $this->usuario->listarDados("email", $email);

            if($usuarioDados === null) {
                error_log("Erro ao consultar usuário para recuperação de senha.");
                http_response_code(200);
                echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Se o e-mail estiver cadastrado, você receberá um link de recuperação."
                ]);
                return;
            }

            //envia mensagem de sucesso mesmo com error
            //evita enumeracao de contas por uma pessoa mal-intencionada
            if($usuarioDados === false) {
                http_response_code(200);
                echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Se o e-mail estiver cadastrado, você receberá um link de recuperação."
                ]);
                return;
            }

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash("sha256", $token);
            $expiracao = (new DateTimeImmutable("+30 minutes"))->format("Y-m-d H:i:sP"); 

            error_log("1 - Antes de criar a recuperação");

            $resultado = $this->recuperacaoSenha->criarRecuperacaoSenha($usuarioDados["id_usuario"], $tokenHash, $expiracao);

            error_log("2 - Recuperação criada. Antes de enviar o e-mail");

            if($resultado !== true) {
                error_log("Erro ao criar token de recuperação.");
                http_response_code(200);
                echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Se o e-mail estiver cadastrado, você receberá um link de recuperação."
                ]);
                return;
            }

            $enviado = $this->emailService->enviarRecuperacao($email, $token);

            error_log("3 - Depois de enviar o e-mail");

            if ($enviado !== true) {

                $exclusao = $this->recuperacaoSenha->excluirToken(
                    $usuarioDados["id_usuario"]
                );

                if ($exclusao !== true) {
                    error_log("Não foi possível excluir o token após falha no envio.");
                }

                error_log("Falha no envio do e-mail de recuperação.");

                http_response_code(200);
                echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Se o e-mail estiver cadastrado, você receberá um link de recuperação."
                ]);
                return;
            }

            
            http_response_code(200);
            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Se o e-mail estiver cadastrado, você receberá um link de recuperação."
            ]);
            return;

        } catch (\Throwable $e) {
            error_log("Erro inesperado na solicitação de recuperação: " . $e->getMessage());
            http_response_code(200);
            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Se o e-mail estiver cadastrado, você receberá um link de recuperação."
            ]);
            return;
        }
    }

    public function redefinirSenha() {
        header("Content-Type: application/json; charset=UTF-8");

        $dados = json_decode(file_get_contents("php://input"), true);

        if(!is_array($dados)) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Dados inválidos."
            ]);

            return;
        }

        $token = $dados["token"] ?? null;
        $senha = $dados["senha"] ?? null;
        $confirmarSenha = $dados["confirmarSenha"] ?? null;

        if(!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
                http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Token inválido."
            ]);
            return;
        }

        if (!is_string($senha) || !is_string($confirmarSenha) || strlen($senha) < 8) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "A senha deve possuir pelo menos 8 caracteres."
            ]);
            return;
        }

        if($senha !== $confirmarSenha) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "erro" => "As senhas não coincidem"
            ]);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            $tokenHash = hash("sha256", $token);
            $tokenDados = $this->recuperacaoSenha->pesquisarToken($tokenHash);

            if($tokenDados === null) {
                throw new runtimeException("Erro ao pesquisar token.");
            }

            if($tokenDados === false) {
                $this->pdo->rollBack();
                http_response_code(400);
                echo json_encode([
                    "sucesso" => false,
                    "erro" => "Token inválido ou expirado."]
                );
                return;
            }

            $idUsuario = $tokenDados["id_usuario"];

            //hash da nova senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            if($senhaHash === false) {
                throw new RuntimeException("Erro ao gerar hash da senha.");
            }

            $resultado = $this->usuario->atualizarColuna($idUsuario, "senha", $senhaHash);

            if($resultado !== true) {
                throw new RuntimeException("Erro ao atualizar senha");
            }

            //invalidar token
            $resultado = $this->recuperacaoSenha->invalidarToken($tokenHash);

            if($resultado !== true) {
                throw new RuntimeException("Erro ao invalidar token");
            }

            //confirmar transacao
            $this->pdo->commit();

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Senha redefinida com sucesso."
            ]);
        } catch (\Throwable $e) {
            if($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro ao redefinir senha: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "erro" => "Não foi possível redefinir a senha. Tente novamente."
            ]);
        }
    }
}