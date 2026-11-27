<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Services\RotaService;

class RotaSeed
{

    public function run(): void
    {
        $pdo = Database::conectar();
        $rotaRepository = new RotaRepository($pdo);
        $rotaService = new RotaService($rotaRepository);

       $rotas = [
            [
                'dados' => [
                    'descricao' => 'Macieira -> Unoesc (Ida)',
                    'ativo' => 1,
                ],
                // A ordem deste array define a sequência das paradas (1, 2, 3...)
                'pontos' => [
                    'Macieira (Fraiburgo)',
                    'Rodoviária (Fraiburgo)',
                    'Camara Municipal (Fraiburgo)',
                    'Policia Civil (Fraiburgo)',
                    'Posto MaxSul (Fraiburgo)',
                    'Unoesc (Videira)',
                ],
            ],
            [
                'dados' => [
                    'descricao' => 'Iomere -> Unoesc (Ida)',
                    'ativo' => 1,
                ],
                'pontos' => [
                    'Brilho da Lua (Iomere)',
                    'Igreja Matriz (Iomere)',
                    'Leão (Iomere)',
                    'Unoesc (Videira)',
                ],
            ],
        ];

        $stmtBuscaRota = $pdo->prepare("SELECT id FROM rota WHERE descricao = ? LIMIT 1");
        $stmtBuscaPonto = $pdo->prepare("SELECT id FROM ponto_de_parada WHERE descricao = ? LIMIT 1");

        foreach ($rotas as $item) {
            $descricaoRota = trim($item['dados']['descricao']);

            // 1. Evita duplicar a rota caso a seed rode mais de uma vez
            $stmtBuscaRota->execute([$descricaoRota]);
            if ($stmtBuscaRota->fetchColumn()) {
                echo "[IGNORADO] Rota já cadastrada: {$descricaoRota}\n";
                continue;
            }

            // 2. Cadastra a rota e obtém o ID gerado
            $rotaId = $rotaService->cadastrar($item['dados']);
            echo "[OK] Rota cadastrada: {$descricaoRota} (ID {$rotaId})\n";

            // 3. Vincula os pontos de parada na sequência definida
            foreach ($item['pontos'] as $descricaoPonto) {
                $stmtBuscaPonto->execute([trim($descricaoPonto)]);
                $pontoId = $stmtBuscaPonto->fetchColumn();

                if (!$pontoId) {
                    echo "   [AVISO] Ponto não encontrado no banco: {$descricaoPonto}\n";
                    continue;
                }

                $rotaService->vincularPonto($rotaId, (int) $pontoId);
                echo "   -> Ponto vinculado: {$descricaoPonto}\n";
            }
        }
    }
}

(new RotaSeed())->run();