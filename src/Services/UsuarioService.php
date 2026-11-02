<?php

namespace Services;

class UsuarioService
{
    private \UsuarioRepository $usuarioRepository;

    public function __construct(\UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function listarPaginado(?string $search, ?string $papelId, int $pagina = 1, int $limite = 10): array
    {
        return $this->usuarioRepository->paginate($limite, $pagina, $search, $papelId);
    }

    public function listarPapeisAtivos(): array
    {
        return $this->usuarioRepository->getAllPapeisAtivos();
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->usuarioRepository->findById($id);
    }

    public function cadastrar(array $dados, array $papeisIds = []): void
    {
        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = $dados['senha'] ?? '';
        $senhaConfirmacao = $dados['senha_confirmation'] ?? '';

        if (empty($nome) || empty($email) || empty($senha)) {
            throw new \InvalidArgumentException('Preencha todos os campos obrigatórios.');
        }

        if ($senha !== $senhaConfirmacao) {
            throw new \InvalidArgumentException('As senhas não coincidem.');
        }

        if ($this->usuarioRepository->findByEmail($email)) {
            throw new \InvalidArgumentException('Este e-mail já está cadastrado.');
        }

        $dadosCriacao = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $dados['telefone'] ?? null,
            'data_nascimento' => $dados['data_nascimento'] ?? null
        ];

        $this->usuarioRepository->create($dadosCriacao, $papeisIds);
    }

    public function atualizar(int $id, array $dados, array $papeisIds = []): void
    {
        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = $dados['senha'] ?? '';
        $senhaConfirmacao = $dados['senha_confirmation'] ?? '';

        if ($id <= 0 || empty($nome) || empty($email)) {
            throw new \InvalidArgumentException('Dados inválidos para atualização.');
        }

        if (!empty($senha) && $senha !== $senhaConfirmacao) {
            throw new \InvalidArgumentException('As senhas não coincidem.');
        }

        // Verifica se o e-mail já pertence a outro usuário
        $usuarioExistente = $this->usuarioRepository->findByEmail($email);
        if ($usuarioExistente && (int)$usuarioExistente['id'] !== $id) {
            throw new \InvalidArgumentException('Este e-mail já está em uso por outro usuário.');
        }

        $dadosAtualizacao = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $dados['telefone'] ?? null,
            'data_nascimento' => $dados['data_nascimento'] ?? null
        ];

        $this->usuarioRepository->update($id, $dadosAtualizacao, $papeisIds);
    }

    public function excluir(int $id, int $usuarioLogadoId): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de usuário inválido.');
        }

        if ($id === $usuarioLogadoId) {
            throw new \InvalidArgumentException('Você não pode excluir sua própria conta.');
        }

        $this->usuarioRepository->delete($id);
    }

    public function autocompleteMotoristas(string $term, ?int $currentUsuarioId = null): array
    {
        if (empty($term)) {
            return [];
        }

        return $this->usuarioRepository->autocompleteMotoristas($term, $currentUsuarioId);
    }
}