<?php

namespace Services;

class VeiculoService
{
    private \VeiculoRepository $veiculoRepository;

    public function __construct(\VeiculoRepository $veiculoRepository)
    {
        $this->veiculoRepository = $veiculoRepository;
    }

    public function listarPaginado(?string $search, ?string $status, int $pagina = 1, int $limite = 10): array
    {
        return $this->veiculoRepository->paginate($limite, $pagina, $search, $status);
    }

    public function buscarPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->veiculoRepository->findById($id);
    }

    public function cadastrar(array $dados): void
    {
        $descricao = trim($dados['descricao'] ?? '');
        $placa = trim($dados['placa'] ?? '');
        $numeroPassageiros = $dados['numero_passageiros'] ?? 0;

        if (empty($descricao) || empty($placa)) {
            throw new \InvalidArgumentException('Preencha todos os campos obrigatórios.');
        }

        $dadosCriacao = [
            'descricao' => $descricao,
            'placa' => $placa,
            'numero_passageiros' => (int) $numeroPassageiros,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->veiculoRepository->create($dadosCriacao);
    }

    public function atualizar(int $id, array $dados): void
    {
        $descricao = trim($dados['descricao'] ?? '');
        $placa = trim($dados['placa'] ?? '');
        $numeroPassageiros = $dados['numero_passageiros'] ?? 0;

        if ($id <= 0 || empty($descricao) || empty($placa)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização.');
        }

        $dadosAtualizacao = [
            'descricao' => $descricao,
            'placa' => $placa,
            'numero_passageiros' => (int) $numeroPassageiros,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->veiculoRepository->update($id, $dadosAtualizacao);
    }

    public function excluir(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de veículo inválido.');
        }

        $this->veiculoRepository->delete($id);
    }

    public function autocomplete(string $term, ?int $currentVeiculoId = null): array
    {
        if (empty($term)) {
            return [];
        }

        return $this->veiculoRepository->autocomplete($term, $currentVeiculoId);
    }
}