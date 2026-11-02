<?php

namespace Services;

class ViagemService
{
    private \ViagemRepository $repository;

    public function __construct(\ViagemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listarPaginado(?string $search, ?string $dataInicio, ?string $dataFim, ?string $status, int $pagina = 1, int $limite = 10): array
    {
        return $this->repository->paginate($limite, $pagina, $search, $dataInicio, $dataFim, $status);
    }

    public function buscarPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findById($id);
    }

    public function obterDadosFormulario(): array
    {
        return [
            'rotas' => $this->repository->getRotasAtivas(),
            'motoristas' => $this->repository->getMotoristasAtivos(),
            'veiculos' => $this->repository->getVeiculosAtivos()
        ];
    }

    public function cadastrar(array $dados): int
    {
        $rotaId = isset($dados['rota_id']) ? (int) $dados['rota_id'] : 0;
        $motoristaId = isset($dados['motorista_id']) ? (int) $dados['motorista_id'] : 0;
        $veiculoId = isset($dados['veiculo_id']) ? (int) $dados['veiculo_id'] : 0;
        $dataSaida = trim($dados['data_hora_saida'] ?? '');

        if ($rotaId <= 0 || $motoristaId <= 0 || $veiculoId <= 0 || empty($dataSaida)) {
            throw new \InvalidArgumentException('Preencha todos os campos obrigatórios da viagem.');
        }

        $dadosCriacao = [
            'rota_id' => $rotaId,
            'motorista_id' => $motoristaId,
            'veiculo_id' => $veiculoId,
            'data_hora_saida' => $dataSaida,
            'data_hora_chegada' => $dados['data_hora_chegada'] ?? null,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        return $this->repository->create($dadosCriacao);
    }

    public function atualizar(int $id, array $dados): void
    {
        $rotaId = isset($dados['rota_id']) ? (int) $dados['rota_id'] : 0;
        $motoristaId = isset($dados['motorista_id']) ? (int) $dados['motorista_id'] : 0;
        $veiculoId = isset($dados['veiculo_id']) ? (int) $dados['veiculo_id'] : 0;
        $dataSaida = trim($dados['data_hora_saida'] ?? '');

        if ($id <= 0 || $rotaId <= 0 || $motoristaId <= 0 || $veiculoId <= 0 || empty($dataSaida)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização.');
        }

        $dadosAtualizacao = [
            'rota_id' => $rotaId,
            'motorista_id' => $motoristaId,
            'veiculo_id' => $veiculoId,
            'data_hora_saida' => $dataSaida,
            'data_hora_chegada' => $dados['data_hora_chegada'] ?? null,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->repository->update($id, $dadosAtualizacao);
    }

    public function excluir(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de viagem inválido.');
        }

        $this->repository->delete($id);
    }

    public function duplicar(int $id): array
    {
        $viagem = $this->buscarPorId($id);
        if (!$viagem) {
            throw new \InvalidArgumentException('Viagem não encontrada para duplicação.');
        }

        // Limpa campos específicos para nova criação
        $viagem['data_hora_saida'] = '';
        $viagem['data_hora_chegada'] = '';
        $viagem['codigo_qr'] = null;

        return $viagem;
    }
}