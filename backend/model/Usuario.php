<?php 
require_once 'Endereco.php':
use DateTime;

class Usuario {
    private int $idUsuario;
    private string $tipo;
    private string $nome;
    private string $sobrenome;
    private string $email;
    private string $senha;
    private string $telefone;
    private DateTime $dataNascimento 
    private string $sexo;
    private string $genero;
    private string $imagemPerfil;
    private Endereco $endereco;

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }

    public function getTipo(): string {
        return $this->tipo;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getSobrenome(): string {
        return $this->sobrenome;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getSenha(): string {
        return $this->senha;
    }

    public function getTelefone(): string {
        return $this->telefone;
    }

    public function getDataNascimento(): DateTime {
        return $this->dataNascimento;
    }

    public function getSexo(): string {
        return $this->sexo;
    }

    public function getGenero(): string {
        return $this->genero;
    }

    public function getImagemPerfil(): string {
        return $this->imagemPerfil;
    }

    public function getEndereco(): Endereco {
        return $this->endereco;
    }
}

?>