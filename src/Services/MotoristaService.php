<?php

namespace Services;

class MotoristaService
{
    private \MotoristaRepository $motoristaRepository;

    public function __construct(\MotoristaRepository $motoristaRepository)
    {
        $this->motoristaRepository = $motoristaRepository;
    }

    public function listarPaginado(?string $search, ?string $status, int $pagina = 1, int $limite = 10): array
    {
        return $this->motoristaRepository->paginate($limite, $pagina, $search, $status);
    }

    public function buscarPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->motoristaRepository->findById($id);
    }

    public function cadastrar(array $dados): void
    {
        $usuarioId = $dados['usuario_id'] ?? null;
        $cnh = trim($dados['cnh'] ?? '');
        $dataValidadeCnh = $dados['data_validade_cnh'] ?? null;

        if (empty($usuarioId) || empty($cnh) || empty($dataValidadeCnh)) {
            throw new \InvalidArgumentException('Por favor, preencha todos os campos obrigatórios.');
        }

        $dadosCriacao = [
            'usuario_id' => (int) $usuarioId,
            'cnh' => $cnh,
            'data_validade_cnh' => $dataValidadeCnh,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->motoristaRepository->create($dadosCriacao);
    }

    public function atualizar(int $id, array $dados): void
    {
        $usuarioId = $dados['usuario_id'] ?? null;
        $cnh = trim($dados['cnh'] ?? '');
        $dataValidadeCnh = $dados['data_validade_cnh'] ?? null;

        if ($id <= 0 || empty($usuarioId) || empty($cnh) || empty($dataValidadeCnh)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização.');
        }

        $dadosAtualizacao = [
            'usuario_id' => (int) $usuarioId,
            'cnh' => $cnh,
            'data_validade_cnh' => $dataValidadeCnh,
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->motoristaRepository->update($id, $dadosAtualizacao);
    }

    public function excluir(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de motorista inválido.');
        }

        $this->motoristaRepository->delete($id);
    }

    public function autocomplete(string $term, ?int $currentMotoristaId = null): array
    {
        if (empty($term)) {
            return [];
        }

        $motoristas = $this->motoristaRepository->autocomplete($term, $currentMotoristaId);

        return array_map(fn($m) => [
            'id' => $m['id'],
            'nome' => $m['nome'] ?? "Motorista #{$m['id']}",
            'email' => $m['email'] ?? ''
        ], $motoristas);
    }
}