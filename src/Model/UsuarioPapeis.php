<?php

class UsuarioPapeis extends Model
{
    private int $id;
    private DateTime $dataHoraInicio;
    private DateTime $dataHoraFim;
    private bool $ativo;
    private Usuario $usuario;
    private Papel $papel;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDataHoraInicio(): DateTime
    {
        return $this->dataHoraInicio;
    }

    public function setDataHoraInicio(DateTime $dataHoraInicio): void
    {
        $this->dataHoraInicio = $dataHoraInicio;
    }

    public function getDataHoraFim(): DateTime
    {
        return $this->dataHoraFim;
    }

    public function setDataHoraFim(DateTime $dataHoraFim): void
    {
        $this->dataHoraFim = $dataHoraFim;
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

    public function getPapel(): Papel
    {
        return $this->papel;
    }

    public function setPapel(Papel $papel): void
    {
        $this->papel = $papel;
    }
}