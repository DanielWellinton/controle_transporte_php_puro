<?php

class Rota extends Model
{
    private int $id;
    private string $descricao;
    private bool $ativo;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDescricao(): float
    {
        return $this->descricao;
    }

    public function setDescricao(float $descricao): void
    {
        $this->descricao = $descricao;
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