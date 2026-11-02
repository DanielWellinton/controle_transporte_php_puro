<?php

namespace Services;

class AuthService
{
    private \UsuarioRepository $usuarioRepository;
    private \PDO $pdo;

    public function __construct(\UsuarioRepository $usuarioRepository, \PDO $pdo)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->pdo = $pdo;
    }

    public function autenticar(string $email, string $senha): array
    {
        $email = trim($email);
        
        if (empty($email) || empty($senha)) {
            throw new \InvalidArgumentException('E-mail e senha são obrigatórios.');
        }

        $usuario = $this->usuarioRepository->verificarCredenciais($email, $senha);

        if (!$usuario) {
            throw new \InvalidArgumentException('E-mail ou senha incorretos.');
        }

        // Busca os papéis ativos do utilizador
        $stmtPapeis = $this->pdo->prepare("
            SELECT p.descricao 
            FROM papel p
            JOIN usuario_papeis up ON p.id = up.papel_id
            WHERE up.usuario_id = ? AND up.ativo = 1
        ");
        $stmtPapeis->execute([$usuario['id']]);
        $papeis = $stmtPapeis->fetchAll(\PDO::FETCH_COLUMN);

        // Dados para o payload do JWT
        $dadosPayload = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'papeis' => $papeis
        ];

        $jwtToken = JwtService::gerarToken($dadosPayload);

        return [
            'usuario' => $usuario,
            'papeis' => $papeis,
            'token' => $jwtToken
        ];
    }

    public function registar(array $dados): void
    {
        $nome = trim($dados['name'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = $dados['password'] ?? '';
        $senhaConf = $dados['password_confirmation'] ?? '';
        $telefone = $dados['telefone'] ?? null;
        $dataNascimento = $dados['data_nascimento'] ?? null;

        if (empty($nome) || empty($email) || empty($senha)) {
            throw new \InvalidArgumentException('Preencha todos os campos obrigatórios.');
        }

        if ($senha !== $senhaConf) {
            throw new \InvalidArgumentException('As senhas não coincidem.');
        }

        if ($this->usuarioRepository->findByEmail($email)) {
            throw new \InvalidArgumentException('Este e-mail já está cadastrado.');
        }

        // Cria o usuário através do repositório
        $dadosCriacao = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'data_nascimento' => $dataNascimento
        ];

        // Se o create do repositório não suportar papéis diretos, podemos usar o método existente ou buscar o ID a seguir
        $this->usuarioRepository->create($dadosCriacao);
        
        $usuarioCadastrado = $this->usuarioRepository->findByEmail($email);
        $novoUsuarioId = $usuarioCadastrado['id'] ?? null;

        if ($novoUsuarioId) {
            try {
                $stmtPapel = $this->pdo->prepare("SELECT id FROM papel WHERE descricao = 'Passageiro' LIMIT 1");
                $stmtPapel->execute();
                $papelPassageiro = $stmtPapel->fetch(\PDO::FETCH_ASSOC);

                if ($papelPassageiro) {
                    $stmtPivot = $this->pdo->prepare("
                        INSERT INTO usuario_papeis (usuario_id, papel_id, ativo, data_hora_inicio) 
                        VALUES (?, ?, 1, NOW())
                    ");
                    $stmtPivot->execute([$novoUsuarioId, $papelPassageiro['id']]);
                }
            } catch (\Exception $e) {
                error_log("Erro ao atribuir papel padrão de passageiro: " . $e->getMessage());
            }
        }
    }
}