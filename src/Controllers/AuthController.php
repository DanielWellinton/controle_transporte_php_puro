<?php

use Services\JwtService;
class AuthController
{
    private PDO $pdo;
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->usuarioRepository = new UsuarioRepository($this->pdo);
    }

    public function showLogin(): void
    {
        if (isset($_SESSION['usuario_id']) || isset($_COOKIE['auth_token'])) {
            // Opcional: Se quiseres validar rigorosamente se o token do cookie ainda é válido antes de redirecionar:
            if (isset($_COOKIE['auth_token']) && JwtService::validarToken($_COOKIE['auth_token'])) {
                header('Location: /dashboard');
                exit;
            }
        }
        
        view('auth.login', [], 'layouts/auth');
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['password'] ?? '';
    
        $usuario = $this->usuarioRepository->verificarCredenciais($email, $senha);
    
        if (!$usuario) {
            $_SESSION['error'] = 'E-mail ou senha incorretos.';
            header('Location: /login');
            exit;
        }

        // 1. Buscar os papéis ativos do utilizador na base de dados
        $stmtPapeis = $this->pdo->prepare("
            SELECT p.descricao 
            FROM papel p
            JOIN usuario_papeis up ON p.id = up.papel_id
            WHERE up.usuario_id = ? AND up.ativo = 1
        ");
        $stmtPapeis->execute([$usuario['id']]);
        $papeis = $stmtPapeis->fetchAll(PDO::FETCH_COLUMN); // Retorna um array simples, ex: ['Passageiro', 'Admin']
    
        // 2. Dados que vão dentro do token (incluindo os papéis)
        $dadosPayload = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'papeis' => $papeis
        ];
    
        // Gera o JWT
        $jwtToken = JwtService::gerarToken($dadosPayload);
    
        // Guarda o JWT num Cookie HTTP-only
        setcookie('auth_token', $jwtToken, [
            'expires' => time() + (86400 * 7),
            'path' => '/',
            'secure' => false,   // true em produção com HTTPS
            'httponly' => true,  
            'samesite' => 'Lax'
        ]);
    
        // 3. Guarda na sessão para acesso rápido e síncrono nas Views
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nome'];
        $_SESSION['user_roles'] = $papeis; // Ex: ['Passageiro', 'Administrador']
    
        header('Location: /dashboard');
        exit;
    }
    
    public function logout(): void
    {
        // Remove o cookie expirando-o
        setcookie('auth_token', '', time() - 3600, '/');
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /dashboard');
            exit;
        }
        view('auth.register', [], 'layouts/auth');
    }

    public function register(): void
    {
        $nome = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? null;
        $dataNascimento = $_POST['data_nascimento'] ?? null;
        $senha = $_POST['password'] ?? '';
        $senhaConf = $_POST['password_confirmation'] ?? '';

        if (empty($nome) || empty($email) || empty($senha)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /register');
            exit;
        }

        if ($senha !== $senhaConf) {
            $_SESSION['error'] = 'As senhas não coincidem.';
            header('Location: /register');
            exit;
        }

        if ($this->usuarioRepository->findByEmail($email)) {
            $_SESSION['error'] = 'Este e-mail já está cadastrado.';
            header('Location: /register');
            exit;
        }

        $dados = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'data_nascimento' => $dataNascimento
        ];

        $this->usuarioRepository->create($dados);
        $usuarioCadastrado = $this->usuarioRepository->findByEmail($email);
        $novoUsuarioId = $usuarioCadastrado['id'] ?? null;

        if ($novoUsuarioId) {
            try {
                $pdo = Database::conectar();

                $stmtPapel = $pdo->prepare("SELECT id FROM papel WHERE descricao = 'Passageiro' LIMIT 1");
                $stmtPapel->execute();
                $papelPassageiro = $stmtPapel->fetch(PDO::FETCH_ASSOC);

                if ($papelPassageiro) {
                    $papelId = $papelPassageiro['id'];

                    $stmtPivot = $pdo->prepare("
                        INSERT INTO usuario_papeis (usuario_id, papel_id, ativo, data_hora_inicio) 
                        VALUES (?, ?, 1, NOW())
                    ");
                    $stmtPivot->execute([$novoUsuarioId, $papelId]);
                }
            } catch (Exception $e) {
                error_log("Erro ao atribuir papel padrão de passageiro: " . $e->getMessage());
            }
        }

        $_SESSION['success'] = 'Conta criada com sucesso! Faça login para continuar.';
        header('Location: /login');
        exit;
    }
}