<?php

class AuthMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Tenta obter o token do Cookie ou do Header HTTP (para APIs/Fetch)
        $token = $_COOKIE['auth_token'] ?? null;

        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        // 2. Se não houver token, bloqueia o acesso
        if (!$token) {
            self::falharAcesso();
        }

        // 3. Valida o token JWT
        $dadosUsuario = JwtService::validarToken($token);

        if (!$dadosUsuario) {
            self::falharAcesso();
        }

        // 4. Injeta os dados validados do utilizador na sessão para uso nos controladores
        $_SESSION['usuario'] = $dadosUsuario;
        $_SESSION['usuario_id'] = $dadosUsuario['id'];
    }

    private static function falharAcesso(): void
    {
        // Se a requisição esperava JSON (ex: chamadas fetch/API), retorna JSON, senão redireciona
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['success' => false, 'message' => 'Sessão expirada ou não autorizada.']);
            exit;
        }

        $_SESSION['error'] = 'Por favor, faça login para continuar.';
        header('Location: /login');
        exit;
    }
}