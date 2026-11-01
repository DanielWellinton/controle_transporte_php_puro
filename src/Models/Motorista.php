<?php

class Motorista extends Model
{
    private int $id;
    private string $cnh;
    private DateTime $dataValidadeCnh;
    private bool $ativo;
    private Usuario $usuario;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCnh(): string
    {
        return $this->cnh;
    }

    public function setCnh(int $cnh): void
    {
        $this->cnh = $cnh;
    }

    public function getdataValidadeCnh(): DateTime
    {
        return $this->dataValidadeCnh;
    }

    public function setdataValidadeCnh(DateTime $dataValidadeCnh): void
    {
        $this->dataValidadeCnh = $dataValidadeCnh;
    }

    public function getAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }
}