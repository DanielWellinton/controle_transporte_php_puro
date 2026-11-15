<?php

use Services\JwtService;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Se já existe uma sessão ativa com o ID do usuário, o acesso é liberado imediatamente
        if (!empty($_SESSION['usuario_id'])) {
            return;
        }

        // 2. Caso contrário, tenta validar via Cookie JWT / Bearer
        $token = $_COOKIE['auth_token'] ?? null;

        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            self::falharAcesso();
        }

        $dadosUsuario = JwtService::validarToken($token);

        if (!$dadosUsuario) {
            self::falharAcesso();
        }

        // Restaura os dados na sessão a partir do token válido
        $_SESSION['usuario_id'] = $dadosUsuario['id'] ?? $dadosUsuario['usuario_id'] ?? $dadosUsuario['sub'] ?? null;
        $_SESSION['usuario'] = $dadosUsuario;

        if (empty($_SESSION['usuario_id'])) {
            self::falharAcesso();
        }
    }

    private static function falharAcesso(): void
    {
        unset($_SESSION['usuario'], $_SESSION['usuario_id']);

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