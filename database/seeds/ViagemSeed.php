<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Services\ViagemService;

class ViagemSeed
{
    public function run(): void
    {
        $pdo = Database::conectar();
        $viagemRepository = new ViagemRepository($pdo);
        $viagemService = new ViagemService($viagemRepository);

        $viagens = [
            [
                'rota_descricao' => 'Macieira -> Unoesc (Ida)',
                'dados' => [
                    'motorista_id' => 1,
                    'veiculo_id' => 2,
                    'data_hora_saida' => '2026-11-26 18:00:00',
                    'data_hora_chegada' => '2026-11-26 19:00:00',
                    'ativo' => 1,
                ],
            ],
            [
                'rota_descricao' => 'Iomere -> Unoesc (Ida)',
                'dados' => [
                    'motorista_id' => 2,
                    'veiculo_id' => 1,
                    'data_hora_saida' => '2026-11-26 18:30:00',
                    'data_hora_chegada' => '2026-11-26 19:0:00',
                    'ativo' => 1,
                ],
            ],
        ];

        $stmtBuscaRota = $pdo->prepare("SELECT id FROM rota WHERE descricao = ? LIMIT 1");
        $stmtBuscaViagem = $pdo->prepare("SELECT id FROM viagem WHERE rota_id = ? AND data_hora_saida = ? LIMIT 1");

        foreach ($viagens as $item) {
            $stmtBuscaRota->execute([trim($item['rota_descricao'])]);
            $rotaId = (int) $stmtBuscaRota->fetchColumn();

            if ($rotaId <= 0) {
                echo "[ERRO] Rota não encontrada: {$item['rota_descricao']}\n";
                continue;
            }

            $dataSaida = $item['dados']['data_hora_saida'];

            // Evita duplicar a mesma viagem caso a seed seja rodada novamente
            $stmtBuscaViagem->execute([$rotaId, $dataSaida]);
            if ($stmtBuscaViagem->fetchColumn()) {
                echo "[IGNORADO] Viagem já cadastrada: {$item['rota_descricao']} ({$dataSaida})\n";
                continue;
            }

            $dadosViagem = array_merge($item['dados'], ['rota_id' => $rotaId]);
            $viagemId = $viagemService->cadastrar($dadosViagem);

            echo "[OK] Viagem cadastrada: {$item['rota_descricao']} em {$dataSaida} (ID {$viagemId})\n";
        }
    }
}

(new ViagemSeed())->run();