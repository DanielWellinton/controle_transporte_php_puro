<?php

namespace Services;

class PontoDeParadaService
{
    private \PontoDeParadaRepository $repository;

    public function __construct(\PontoDeParadaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listarPaginado(?string $search, ?string $status, int $pagina = 1, int $limite = 10): array
    {
        return $this->repository->paginate($limite, $pagina, $search, $status);
    }

    public function buscarPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findById($id);
    }

    public function cadastrar(array $dados): void
    {
        $descricao = trim($dados['descricao'] ?? '');
        $latitude  = trim($dados['latitude'] ?? '');
        $longitude = trim($dados['longitude'] ?? '');

        if (empty($descricao) || empty($latitude) || empty($longitude)) {
            throw new \InvalidArgumentException('Preencha a descrição e selecione a localização no mapa.');
        }

        $dadosCriacao = [
            'descricao' => $descricao,
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'ativo'     => isset($dados['ativo']) ? 1 : 0
        ];

        $this->repository->create($dadosCriacao);
    }

    public function atualizar(int $id, array $dados): void
    {
        $descricao = trim($dados['descricao'] ?? '');
        $latitude  = trim($dados['latitude'] ?? '');
        $longitude = trim($dados['longitude'] ?? '');

        if ($id <= 0 || empty($descricao) || empty($latitude) || empty($longitude)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização.');
        }

        $dadosAtualizacao = [
            'descricao' => $descricao,
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'ativo'     => isset($dados['ativo']) ? 1 : 0
        ];

        $this->repository->update($id, $dadosAtualizacao);
    }

    public function excluir(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de ponto de parada inválido.');
        }

        $this->repository->delete($id);
    }

    public function autocomplete(string $term, ?int $rotaId = null): array
    {
        $term = trim($term);
        if (empty($term)) {
            return [];
        }

        return $this->repository->autocomplete($term, $rotaId);
    }
}