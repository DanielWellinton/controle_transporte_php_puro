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

        $usuarioId = $_SESSION['usuario_id'] ?? 0;
        
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
                // Define o código HTTP como 403 Forbidden
                http_response_code(403);

                // Se a requisição veio via Fetch/AJAX, retorna JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado para o seu perfil.']);
                    exit;
                }

                view('errors.403', [], "layouts/auth");
                exit;
            }

        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro interno ao validar permissões: ' . $e->getMessage();
            view('errors.403', [], "layouts/auth");
            exit;
        }
    }
}