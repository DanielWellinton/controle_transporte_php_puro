<?php

use Services\JwtService;
use Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $pdo = Database::conectar();
        $usuarioRepository = new UsuarioRepository($pdo);
        $this->authService = new AuthService($usuarioRepository, $pdo);
    }

    public function showLogin(): void
    {
        if (isset($_SESSION['usuario_id']) || isset($_COOKIE['auth_token'])) {
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

        try {
            $resultado = $this->authService->autenticar($email, $senha);

            // Guarda o JWT num Cookie HTTP-only
            setcookie('auth_token', $resultado['token'], [
                'expires' => time() + (86400 * 7),
                'path' => '/',
                'secure' => false,   // true em produção com HTTPS
                'httponly' => true,  
                'samesite' => 'Lax'
            ]);

            // Guarda dados essenciais na sessão
            $_SESSION['usuario_id'] = $resultado['usuario']['id'];
            $_SESSION['user_name'] = $resultado['usuario']['nome'];
            $_SESSION['user_roles'] = $resultado['papeis'];

            header('Location: /dashboard');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Ocorreu um erro ao efetuar login.';
            header('Location: /login');
            exit;
        }
    }
    
    public function logout(): void
    {
        setcookie('auth_token', '', time() - 3600, '/');
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /dashboard');
            exit;
        }
        view('auth.register', [], 'layouts/auth');
    }

    public function register(): void
    {
        try {
            $this->authService->registar($_POST);

            $_SESSION['success'] = 'Conta criada com sucesso! Faça login para continuar.';
            header('Location: /login');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /register');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao registar a conta.';
            header('Location: /register');
            exit;
        }
    }
}