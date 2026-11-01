<?php

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
        if (isset($_SESSION['usuario'])) {
            header('Location: /dashboard');
            exit;
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

        // Armazena dados essenciais na sessão
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email']
        ];
        $_SESSION['usuario_id'] = $usuario['id']; // útil para verificações como o destroy do UserController

        header('Location: /dashboard');
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

        // Regista por padrão sem papel específico ou atribui um papel padrão se necessário
        $this->usuarioRepository->create($dados);

        $_SESSION['success'] = 'Conta criada com sucesso! Faça login para continuar.';
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        unset($_SESSION['usuario']);
        unset($_SESSION['usuario_id']);
        session_destroy();
        header('Location: /login');
        exit;
    }
}