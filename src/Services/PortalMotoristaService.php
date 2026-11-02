<?php

namespace Services;

class PortalMotoristaService
{
    private \PortalMotoristaRepository $repository;

    public function __construct(\PortalMotoristaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function verificarMotorista(array $user): array
    {
        // Verifica papel de motorista ou administrador
        if (($user['role'] ?? '') !== 'motorista' && !($user['is_admin'] ?? false)) {
            throw new \RuntimeException("Usuário não possui o papel de motorista.", 403);
        }

        $motorista = $this->repository->findMotoristaByUserId((int) $user['id']);

        if (!$motorista) {
            throw new \InvalidArgumentException('Seu cadastro de motorista ainda não foi concluído.');
        }

        return $motorista;
    }

    public function autorizarMotorista(array $motorista, array $viagem, array $user): void
    {
        $isAdmin = $user['is_admin'] ?? false;

        if ($viagem['motorista_id'] != $motorista['id'] && !$isAdmin) {
            throw new \RuntimeException("Você não tem permissão para gerenciar esta viagem.", 403);
        }
    }

    public function listarViagensAtivas(int $motoristaId): array
    {
        return $this->repository->getViagensAtivasByMotorista($motoristaId);
    }

    public function listarHistorico(int $motoristaId, ?string $search, ?string $dataInicio, ?string $dataFim, int $pagina = 1): array
    {
        return $this->repository->paginateHistorico($motoristaId, 10, $pagina, $search, $dataInicio, $dataFim);
    }

    public function buscarDetalhesViagem(int $viagemId, array $motorista, array $user): array
    {
        if ($viagemId <= 0) {
            throw new \InvalidArgumentException('Viagem não encontrada.');
        }

        $viagem = $this->repository->findViagemDetalhes($viagemId);

        if (!$viagem) {
            throw new \InvalidArgumentException('Viagem não encontrada.');
        }

        $this->autorizarMotorista($motorista, $viagem, $user);

        return $viagem;
    }

    public function iniciarViagem(int $viagemId, array $motorista, array $user): void
    {
        $viagem = $this->buscarDetalhesViagem($viagemId, $motorista, $user);
        $this->repository->iniciarViagem($viagem['id']);
    }

    public function finalizarViagem(int $viagemId, array $motorista, array $user): void
    {
        $viagem = $this->buscarDetalhesViagem($viagemId, $motorista, $user);
        $this->repository->finalizarViagem($viagem['id']);
    }
}