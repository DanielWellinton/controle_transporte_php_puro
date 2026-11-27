<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Services\VeiculoService;

class VeiculoSeed
{
    public function run(): void
    {
        $pdo = Database::conectar();
        $veiculoRepository = new VeiculoRepository($pdo);
        $veiculoService = new VeiculoService($veiculoRepository);

        $veiculos = [
            [
                'dados' => [
                    'descricao' => 'Ônibus Executivo 01',
                    'placa' => 'ABC1D23',
                    'numero_passageiros' => 44,
                    'ativo' => 1,
                ],
            ],
            [
                'dados' => [
                    'descricao' => 'Micro-ônibus 02',
                    'placa' => 'DEF4G56',
                    'numero_passageiros' => 28,
                    'ativo' => 1,
                ],
            ],
        ];

        foreach ($veiculos as $item) {
            $placa = strtoupper(trim($item['dados']['placa']));

            // Verifica se já existe veículo com a mesma placa
            $stmt = $pdo->prepare("SELECT id FROM veiculo WHERE placa = ? LIMIT 1");
            $stmt->execute([$placa]);

            if ($stmt->fetchColumn()) {
                echo "[IGNORADO] Veículo já cadastrado com a placa {$placa}.\n";
                continue;
            }

            $veiculoService->cadastrar($item['dados']);
            echo "[OK] Veículo cadastrado: {$item['dados']['descricao']} ({$placa})\n";
        }
    }
}

(new VeiculoSeed())->run();