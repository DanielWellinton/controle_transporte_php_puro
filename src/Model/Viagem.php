<?php

class Viagem extends Model
{
    private int $id;
    private string $codigoQr;
    private DateTime $dataHoraSaida;
    private ?DateTime $dataHoraChegada;
    private Rota $rota;
    private Motorista $motorista;
    private Veiculo $veiculo;
    private bool $ativo;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCodigoQr(): string
    {
        return $this->codigoQr;
    }

    public function setCodigoQr(string $codigoQr): void
    {
        $this->codigoQr = $codigoQr;
    }

    public function getDataHoraSaida(): DateTime
    {
        return $this->dataHoraSaida;
    }

    public function setDataHoraSaida(DateTime $dataHoraSaida): void
    {
        $this->dataHoraSaida = $dataHoraSaida;
    }

    public function getDataHoraChegada(): ?DateTime
    {
        return $this->dataHoraChegada;
    }

    public function setDataHoraChegada(?DateTime $dataHoraChegada): void
    {
        $this->dataHoraChegada = $dataHoraChegada;
    }

    public function getRota(): Rota
    {
        return $this->rota;
    }

    public function setRota(Rota $rota): void
    {
        $this->rota = $rota;
    }

    public function getMotorista(): Motorista
    {
        return $this->motorista;
    }

    public function setMotorista(Motorista $motorista): void
    {
        $this->motorista = $motorista;
    }

    public function getVeiculo(): Veiculo
    {
        return $this->veiculo;
    }

    public function setVeiculo(Veiculo $veiculo): void
    {
        $this->veiculo = $veiculo;
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