<?php 
class Endereco{
    private int $idEndereco;
    private string $estado;
    private string $cidade;
    private string $bairro;
    private string $logradouro;
    private string $numero;
    private string $complemento;

    public function getIdEndereco(): int {
        return $this->idEndereco;
    } 

    public function getEstado(): string {
        return $this->estado;
    }

    public function getCidade(): string {
        return $this->cidade;
    }

    public function getBairro(): string {
        return $this->bairro;
    }

    public function getLogradouro(): string {
        return $this->logradouro;
    }

    public function getNumero(): string {
        return $this->numero;
    }

    public function getComplemento(): string {
        return $this->complemento;
    }
}
?>