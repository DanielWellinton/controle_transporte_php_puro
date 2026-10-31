<?php

class Veiculo extends Model
{
    private int $id;
    private string $descricao;
    private string $placa;
    private int $numeroPassageiros;
    private bool $ativo;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function getPlaca(): string
    {
        return $this->placa;
    }

    public function setPlaca(string $placa): void
    {
        $this->placa = $placa;
    }

    public function getNumeroPassageiros(): int
    {
        return $this->numeroPassageiros;
    }

    public function setNumeroPassageiros(int $numeroPassageiros): void
    {
        $this->numeroPassageiros = $numeroPassageiros;
    }

    public function getAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }
}