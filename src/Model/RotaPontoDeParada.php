<?php

class RotaPontoDeParada extends Model
{
    private int $id;
    private Rota $rota;
    private PontoDeParada $pontoDeParada;
    private int $ordem;
    private bool $ativo;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRota(): Rota
    {
        return $this->rota;
    }

    public function setRota(Rota $rota): void
    {
        $this->rota = $rota;
    }

    public function getPontoDeParada(): PontoDeParada
    {
        return $this->pontoDeParada;
    }

    public function setPontoDeParada(PontoDeParada $pontoDeParada): void
    {
        $this->pontoDeParada = $pontoDeParada;
    }

    public function getOrdem(): int
    {
        return $this->ordem;
    }

    public function setOrdem(int $ordem): void
    {
        $this->ordem = $ordem;
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