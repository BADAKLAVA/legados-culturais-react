<?php

require_once __DIR__ . "/../model/Repositorio.php";

class RepositorioController {
    private Repositorio $repositorio;
    private Tag $tagObj;

    public function __construct(Repositorio $repositorio, Tag $tagObj) {
        $this->repositorio = $repositorio;
        $this->tagObj = $tagObj;
    }

    public function criar() {
        header("Content-Type: application/json; charset=utf-8");
        error_log("Iniciou o método de criar");

        $dados = json_decode(file_get_contents("php://input"), true);

        $resultado = null;
        $idRepositorio = null;

        error_log("JSON recebido: " . json_encode(
            array_keys($dados ?? [])
        ));

        $titulo = trim($dados["title"] ?? '');
        $descricao = trim($dados["description"] ?? '');
        $descricao = $descricao === '' ? null : $descricao;
        $caminho = trim($dados["fileName"] ?? '');
        $tipo = trim($dados["fileType"] ?? '');

        $tags = $dados["tags"] ?? [];

        $dataRepositorio = new DateTime(
            'now',
            new DateTimeZone('America/Sao_Paulo')
        );

        $dataRepositorio = $dataRepositorio->format('Y-m-d H:i:s');

        try {

            // Validação dos dados
            if ($this->validarDados(
                $titulo,
                $caminho,
                $tipo
            ) === false) {
                return;
            }

            // Validação das tags
            $tags = $this->validarTag($tags);

            if ($tags === false) {
                return;
            }

            // Criação do repositório
            $resultado = $this->repositorio->criarRepositorio(
                $titulo,
                $descricao,
                $tipo,
                $caminho,
                $dataRepositorio
            );

            if ($resultado === false || $resultado === null) {
                http_response_code(500);

                echo json_encode([
                    "sucesso" => false,
                    "erro" => "Erro ao criar o repositório. Tente novamente."
                ]);

                return;
            }

            // Recuperação do ID do repositório
            $idRepositorio = $resultado;

            // Processamento das tags
            foreach ($tags as $tag) {

                $existente = $this->tagObj->consultarTag($tag);

                if ($existente === null) {
                    throw new Exception("Erro ao consultar tag: " . $tag);
                }

                // Se a tag já existir
                if ($existente !== false) {

                    $idTag = $existente["id_tag"];

                } else {

                    // Criação da tag
                    if ($this->criarTag($tag) !== true) {
                        throw new Exception("Erro ao criar tag: " . $tag);
                    }

                    // Consulta novamente para recuperar o ID
                    $existente = $this->tagObj->consultarTag($tag);

                    if ($existente === false || $existente === null) {
                        throw new Exception("Erro ao recuperar ID da tag: " . $tag);
                    }

                    $idTag = $existente["id_tag"];
                }

                // Associação da tag ao repositório
                $resultado = $this->tagObj->relacionarUsuario(
                    $idTag,
                    $idRepositorio
                );

                if ($resultado !== true) {
                    throw new Exception("Erro ao associar tag ao repositório.");
                }
            }

            // Resposta final
            echo json_encode([
                "sucesso" => "Repositorio criado com sucesso"
            ]);

        } catch (Throwable $e) {

            error_log("ERRO: " . $e->getMessage());
            error_log("RESULTADO: " . var_export($resultado, true));

            http_response_code(500);

            echo json_encode([
                "sucesso" => false,
                "erro" => "Erro ao criar repositório"
            ]);

            return;
        }
    }
  
    public function criarTag($nome) {
        try {
            return $this->tagObj->criarTag($nome);

        } catch (Throwable $e) {
            error_log("ERRO: " . $e->getMessage());
            return false;
        }
    }

    public function validarDados($titulo, $caminho, $tipo) {
        if(empty($titulo)) {
            http_response_code(400);
            error_log("titulo nao existe");
            echo json_encode([
                "sucesso" => false,
                "erro" => "O título não pode estar vazio"
            ]);
            return false;
        }

        if(empty($caminho)) {
            http_response_code(400);
            error_log("caminho nao existe");
            echo json_encode([
                "sucesso" => false,
                "erro" => "Erro no caminho do arquivo"
            ]);
            return false;
        }

        $tiposPermitidos = [".txt", ".pdf", ".docx", ".doc", ".png", ".jpg", ".jpeg", ".webp"];

        if(empty($tipo) ) {
            http_response_code(400);
            error_log("tipo de arquivo nao existe");
            echo json_encode([
                "sucesso" => false,
                "erro" => "Erro no tipo de arquivo"
            ]);
            return false;
        }

        if(!in_array($tipo, $tiposPermitidos, true)) {
            http_response_code(400);
            error_log("tipo de arquivo nao permitido");
            echo json_encode([
                "sucesso" => false,
                "erro" => "O tipo de arquivo não é suportado"
            ]);
            return false;
        }
        return true;
    }

    public function validarTag($tags) {
        if (!is_array($tags)) {
            http_response_code(400);
            error_log("tag nao eh uma array");
            echo json_encode([
                "sucesso" => false,
                "erro" => "Uma ou mais tags estão inválidas"
            ]);
            return false;
        } else {
            foreach ($tags as $tag) {
                if (!is_string($tag) || trim($tag) === '') {
                    http_response_code(400);
                    echo json_encode([
                        "sucesso" => false,
                        "erro" => "Uma ou mais tags estão inválidas"
                    ]);
                    return false;
                }
            }
            return array_unique(array_map('trim', $tags));
        }
        
    }

    public function exibir() {
        error_log("Chegou no exibir repositorio");

        $resposta = $this->repositorio->exibirRepositorio();
        error_log(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if($resposta === null) {
             http_response_code(500);

             echo json_encode([
                "sucesso" => false,
                "erro" => "Erro ao carregar os repositórios"
             ]);

             return;
        }

        echo json_encode([
            "sucesso" => true,
            "repositorios" => $resposta
        ]);
    }
}
