<?php

class RoleMiddleware
{
    /**
     * Verifica se o utilizador autenticado possui um dos papéis permitidos.
     * 
     * @param string|array $papeisPermitidos Ex: 'Admin' ou ['Admin', 'Motorista']
     */
    public static function check($papeisPermitidos): void
    {
        // Garante que está autenticado primeiro
        AuthMiddleware::handle();

        $usuarioId = $_SESSION['usuario']['id'] ?? 0;
        
        if (empty($usuarioId)) {
            header('Location: /login');
            exit;
        }

        if (is_string($papeisPermitidos)) {
            $papeisPermitidos = [$papeisPermitidos];
        }

        try {
            $pdo = Database::conectar();
            
            // Verifica se o utilizador possui algum dos papéis ativos na tabela pivot
            $placeholders = implode(',', array_fill(0, count($papeisPermitidos), '?'));
            $sql = "SELECT COUNT(1) FROM usuario_papeis up
                    JOIN papel p ON up.papel_id = p.id
                    WHERE up.usuario_id = ? 
                      AND up.ativo = 1 
                      AND (up.data_hora_fim IS NULL OR up.data_hora_fim > NOW())
                      AND p.ativo = 1
                      AND p.descricao IN ($placeholders)";

            $stmt = $pdo->prepare($sql);
            
            $params = array_merge([$usuarioId], $papeisPermitidos);
            $stmt->execute($params);
            
            $temPermissao = (int) $stmt->fetchColumn() > 0;

            if (!$temPermissao) {
                $_SESSION['error'] = 'Acesso não autorizado para o seu perfil.';
                header('Location: /dashboard'); // Redireciona para o painel padrão ou 403
                exit;
            }

        } catch (Exception $e) {
            error_log("Erro ao verificar papéis: " . $e->getMessage());
            $_SESSION['error'] = 'Erro interno ao validar permissões.';
            header('Location: /dashboard');
            exit;
        }
    }
}