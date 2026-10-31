<?php

class Passageiro extends Model
{
    private int $id;
    private Viagem $viagem;
    private DateTime $dataHoraSaida;
    private DateTime $dataHoraChegada;
    private RotaPontoDeParada $pontoDeParadaSaida;
    private RotaPontoDeParada $pontoDeParadaChegada;
    private Usuario $usuario;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getViagem(): Viagem
    {
        return $this->viagem;
    }

    public function setViagem(Viagem $viagem): void
    {
        $this->viagem = $viagem;
    }

    public function getDataHoraSaida(): DateTime
    {
        return $this->dataHoraSaida;
    }

    public function setDataHoraSaida(DateTime $dataHoraSaida): void
    {
        $this->dataHoraSaida = $dataHoraSaida;
    }

    public function getDataHoraChegada(): DateTime
    {
        return $this->dataHoraChegada;
    }

    public function setDataHoraChegada(DateTime $dataHoraChegada): void
    {
        $this->dataHoraChegada = $dataHoraChegada;
    }

    public function getPontoDeParadaSaida(): RotaPontoDeParada
    {
        return $this->pontoDeParadaSaida;
    }

    public function setPontoDeParadaSaida(RotaPontoDeParada $pontoDeParadaSaida): void
    {
        $this->pontoDeParadaSaida = $pontoDeParadaSaida;
    }

    public function getPontoDeParadaChegada(): RotaPontoDeParada
    {
        return $this->pontoDeParadaChegada;
    }

    public function setPontoDeParadaChegada(RotaPontoDeParada $pontoDeParadaChegada): void
    {
        $this->pontoDeParadaChegada = $pontoDeParadaChegada;
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