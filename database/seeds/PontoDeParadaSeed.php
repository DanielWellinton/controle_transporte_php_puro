<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Services\PontoDeParadaService;

class PontoDeParadaSeed     
{

    public function run(): void
    {
        $pdo = Database::conectar();
        $pontoDeParadaRepository = new PontoDeParadaRepository($pdo);
        $pontoDeParadaService = new PontoDeParadaService($pontoDeParadaRepository);

        $pontos = [

            [
                'dados' => [
                    'descricao' => 'Macieira (Fraiburgo)',
                    'latitude' => '-27.0792647',
                    'longitude' => '-50.8956242',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Rodoviária (Fraiburgo)',
                    'latitude' => '-27.0308507',
                    'longitude' => '-50.9143406',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Camara Municipal (Fraiburgo)',
                    'latitude' => '-27.0233387',
                    'longitude' => '-50.9118462',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Policia Civil (Fraiburgo)',
                    'latitude' => '-27.0209398',
                    'longitude' => '-50.9170175',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Posto MaxSul (Fraiburgo)',
                    'latitude' => '-27.0188132',
                    'longitude' => '-50.9340361',
                    'ativo' => '1',
                ],
            ],



            [
                'dados' => [
                    'descricao' => 'Unoesc (Videira)',
                    'latitude' => '-26.9927698',
                    'longitude' => '-51.1760223',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Leão (Iomere)',
                    'latitude' => '-27.00144400',
                    'longitude' => '-51.23725120',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Igreja Matriz (Iomere)',
                    'latitude' => '-27.00277630',
                    'longitude' => '-51.24121450',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'descricao' => 'Brilho da Lua (Iomere)',
                    'latitude' => '-27.00075810',
                    'longitude' => '-51.24533820',
                    'ativo' => '1',
                ],
            ],
            
        ];

foreach ($pontos as $item) {
            $descricao = trim($item['dados']['descricao']);

            // Verifica se já existe um ponto de parada com a mesma descrição
            $stmt = $pdo->prepare("SELECT id FROM ponto_de_parada WHERE descricao = ? LIMIT 1");
            $stmt->execute([$descricao]);

            if ($stmt->fetchColumn()) {
                echo "[IGNORADO] Ponto de parada já cadastrado: {$descricao}\n";
                continue;
            }

            $pontoDeParadaService->cadastrar($item['dados']);
            $novoId = $pdo->lastInsertId();
            echo "[OK] Ponto de parada cadastrado: {$descricao} (ID {$novoId})\n";
        }
    }
}

(new PontoDeParadaSeed())->run();