<?php

require_once __DIR__ . "/../model/Repositorio.php";

class RepositorioController {
    private Repositorio $repositorio;

    public function __construct(Repositorio $repositorio) {
        $this->repositorio = $repositorio;
    }

    public function exibirRepositorio() {
        error_log("Chegou no exibir repositorio");

        $resposta = $this->repositorio->exibirRepositorio();
        error_log(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if($resposta === null) {
             http_response_code(500);

             json_encode([
                "sucesso" => false,
                "erro" => "Erro ao carregar os repositórios"
             ]);

             return;
        }
    }
}