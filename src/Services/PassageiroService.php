<?php

namespace Services;

class PassageiroService
{
    private \PassageiroRepository $repository;

    public function __construct(\PassageiroRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listarDashboard(int $userId, ?string $search, ?string $dataInicio, ?string $dataFim, ?string $inscricao, int $pagina = 1): array
    {
        return $this->repository->paginateDashboard($userId, 9, $pagina, $search, $dataInicio, $dataFim, $inscricao);
    }

    public function buscarDetalhesViagem(int $viagemId, int $userId): array
    {
        if ($viagemId <= 0) {
            throw new \InvalidArgumentException('Viagem inválida.');
        }

        $viagem = $this->repository->findViagemDetalhes($viagemId);

        if (!$viagem) {
            throw new \InvalidArgumentException('Viagem não encontrada.');
        }

        $passageiro = $this->repository->findInscricaoPassageiro($userId, $viagemId);

        return [
            'viagem' => $viagem,
            'passageiro' => $passageiro
        ];
    }

    public function salvarPontos(int $userId, array $dados): void
    {
        $viagemId = isset($dados['viagem_id']) ? (int) $dados['viagem_id'] : 0;
        $pontoSaidaId = isset($dados['ponto_de_parada_saida_id']) ? (int) $dados['ponto_de_parada_saida_id'] : 0;
        $pontoChegadaId = isset($dados['ponto_de_parada_chegada_id']) ? (int) $dados['ponto_de_parada_chegada_id'] : 0;

        if ($viagemId <= 0 || !$pontoSaidaId || !$pontoChegadaId) {
            throw new \InvalidArgumentException('Selecione os pontos de embarque e desembarque.');
        }

        $this->repository->salvarPontosInscricao($userId, $viagemId, $pontoSaidaId, $pontoChegadaId);
    }

    public function cancelarInscricao(int $userId, int $viagemId): void
    {
        if ($viagemId <= 0) {
            throw new \InvalidArgumentException('ID de viagem inválido.');
        }

        $this->repository->cancelarInscricao($userId, $viagemId);
    }

    public function listarHistorico(int $userId, ?string $search, ?string $dataInicio, ?string $dataFim, ?string $statusEmbarque, int $pagina = 1): array
    {
        $resultado = $this->repository->paginateHistorico($userId, 10, $pagina, $search, $dataInicio, $dataFim, $statusEmbarque);

        // Recolhe todos os IDs de pontos de parada envolvidos para exibir descrições amigáveis
        $pontosIds = [];
        foreach ($resultado['data'] as $v) {
            foreach ($v['passageiros'] as $p) {
                if (!empty($p['pivot_ponto_de_parada_saida_id'])) $pontosIds[] = $p['pivot_ponto_de_parada_saida_id'];
                if (!empty($p['pivot_ponto_de_parada_chegada_id'])) $pontosIds[] = $p['pivot_ponto_de_parada_chegada_id'];
            }
        }
        
        $pontos = $this->repository->getDescricoesPontos(array_unique($pontosIds));

        return [
            'viagens' => $resultado,
            'pontos' => $pontos
        ];
    }
}