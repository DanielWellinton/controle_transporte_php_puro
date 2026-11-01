<?php

abstract class Model
{
    protected ?DateTime $criado_at = null;
    protected ?DateTime $atualizado_at = null;

    public function getCriadoAt(): ?DateTime
    {
        return $this->criado_at;
    }

    public function setCriadoAt(DateTime|string $criado_at): void
    {
        $this->criado_at = is_string($criado_at) ? new DateTime($criado_at) : $criado_at;
    }

    public function getAtualizadoAt(): ?DateTime
    {
        return $this->atualizado_at;
    }

    public function setAtualizadoAt(DateTime|string $atualizado_at): void
    {
        $this->atualizado_at = is_string($atualizado_at) ? new DateTime($atualizado_at) : $atualizado_at;
    }
}