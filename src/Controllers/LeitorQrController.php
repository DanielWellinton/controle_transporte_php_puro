<?php

class LeitorQrController
{
    /**
     * Exibe a view do scanner de QR Code.
     */
    public function exibirScanner(): void
    {
        // Podes renderizar a view do scanner respetiva
        $title = 'Scanner de QR Code';
        require_once __DIR__ . '/../Views/passageiros/scanner.php';
    }

    /**
     * Valida o QR Code e regista o embarque do passageiro.
     */
    public function validarQrCode(): void
    {
        // Garante que a resposta seja JSON (requisições via Fetch / AJAX)
        header('Content-Type: application/json');

        // Obtém os dados enviados no corpo da requisição (JSON ou POST)
        $input = json_decode(file_get_contents('php://input'), true);
        $codigoQr = $input['codigo_qr'] ?? $_POST['codigo_qr'] ?? null;

        if (empty($codigoQr)) {
            echo json_encode([
                'success' => false,
                'message' => 'O código QR é obrigatório.'
            ]);
            http_response_code(422);
            exit;
        }

        // Se o valor for uma URL, extrai o UUID/código do final da URL
        if (filter_var($codigoQr, FILTER_VALIDATE_URL)) {
            $codigoQr = basename(parse_url($codigoQr, PHP_URL_PATH));
        }

        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            echo json_encode([
                'success' => false,
                'message' => 'Utilizador não autenticado.'
            ]);
            http_response_code(401);
            exit;
        }

        try {
            $pdo = Database::conectar();

            // 1. Busca a viagem pelo QR Code
            $stmtViagem = $pdo->prepare("SELECT id, ativo FROM viagem WHERE codigo_qr = ? LIMIT 1");
            $stmtViagem->execute([$codigoQr]);
            $viagem = $stmtViagem->fetch(PDO::FETCH_ASSOC);

            if (!$viagem) {
                echo json_encode([
                    'success' => false,
                    'message' => 'QR Code inválido ou viagem não encontrada.'
                ]);
                http_response_code(404);
                exit;
            }

            if (empty($viagem['ativo']) || $viagem['ativo'] == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Esta viagem não está mais ativa.'
                ]);
                http_response_code(422);
                exit;
            }

            // 2. Localiza a inscrição do passageiro nesta viagem
            // (Assumindo que a tabela de ligação ou tabela de passageiros/inscrições possui usuario_id e viagem_id)
            $stmtPassageiro = $pdo->prepare("
                SELECT * FROM passageiro 
                WHERE usuario_id = ? AND viagem_id = ? 
                LIMIT 1
            ");
            $stmtPassageiro->execute([$usuarioId, $viagem['id']]);
            $passageiro = $stmtPassageiro->fetch(PDO::FETCH_ASSOC);

            if (!$passageiro) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Você não está cadastrado nesta viagem.'
                ]);
                http_response_code(403);
                exit;
            }

            // 3. Verifica se a presença já foi confirmada anteriormente
            if (!empty($passageiro['data_hora_saida'])) {
                $dataFormatada = date('d/m/Y H:i', strtotime($passageiro['data_hora_saida']));
                echo json_encode([
                    'success' => true,
                    'already_confirmed' => true,
                    'message' => "Embarque já realizado em {$dataFormatada}."
                ]);
                exit;
            }

            // 4. Registra a data/hora atual no campo data_hora_saida
            $stmtUpdate = $pdo->prepare("
                UPDATE passageiro 
                SET data_hora_saida = NOW() 
                WHERE usuario_id = ? AND viagem_id = ?
            ");
            $stmtUpdate->execute([$usuarioId, $viagem['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Presença confirmada! Embarque registrado com sucesso.'
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Erro no LeitorQrController: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Ocorreu um erro interno ao processar o QR Code.'
            ]);
            http_response_code(500);
            exit;
        }
    }
}