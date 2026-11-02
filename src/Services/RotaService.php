<?php

namespace Services;

class RotaService
{
    private \RotaRepository $repository;

    public function __construct(\RotaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listarPaginado(?string $search, ?string $status, int $pagina = 1, int $limite = 10): array
    {
        return $this->repository->paginate($limite, $pagina, $search, $status);
    }

    public function buscarComDetalhes(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $rota = $this->repository->findById($id);
        if (!$rota) {
            return null;
        }

        $rota['pontosDeParada'] = $this->repository->getPontosVinculados($id);
        return $rota;
    }

    public function buscarParaEdicao(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $rota = $this->repository->findById($id);
        if (!$rota) {
            return null;
        }

        $rota['pontosDeParada'] = $this->repository->getPontosVinculados($id);
        $rota['pontosDisponiveis'] = $this->repository->getPontosDisponiveis($id);

        return $rota;
    }

    public function cadastrar(array $dados): int
    {
        $descricao = trim($dados['descricao'] ?? '');

        if (empty($descricao)) {
            throw new \InvalidArgumentException('A descrição da rota é obrigatória.');
        }

        $dadosCriacao = [
            'descricao' => $descricao,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        return $this->repository->create($dadosCriacao);
    }

    public function atualizar(int $id, array $dados): void
    {
        $descricao = trim($dados['descricao'] ?? '');

        if ($id <= 0 || empty($descricao)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização.');
        }

        $dadosAtualizacao = [
            'descricao' => $descricao,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->repository->update($id, $dadosAtualizacao);
    }

    public function excluir(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de rota inválido.');
        }

        $this->repository->delete($id);
    }

    public function vincularPonto(int $rotaId, int $pontoId): void
    {
        if ($rotaId <= 0 || $pontoId <= 0) {
            throw new \InvalidArgumentException('Dados inválidos para vínculo do ponto.');
        }

        $this->repository->vincularPonto($rotaId, $pontoId);
    }

    public function desvincularPonto(int $rotaId, int $pontoId): void
    {
        if ($rotaId <= 0 || $pontoId <= 0) {
            throw new \InvalidArgumentException('Dados inválidos para desvincular o ponto.');
        }

        $this->repository->desvincularPonto($rotaId, $pontoId);
    }

    public function atualizarPontosPivot(int $rotaId, array $pontos): void
    {
        if ($rotaId <= 0 || empty($pontos)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização dos pontos da rota.');
        }

        $this->repository->atualizarPontosPivot($rotaId, $pontos);
    }

    public function autocomplete(string $term, ?int $currentRotaId = null): array
    {
        $term = trim($term);
        if (empty($term)) {
            return [];
        }

        return $this->repository->autocomplete($term, $currentRotaId);
    }
}