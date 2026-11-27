<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Services\MotoristaService;

class MotoristaSeed
{

    public function run(): void
    {
        $pdo = Database::conectar();
        $motoristaRepository = new MotoristaRepository($pdo);
        $motoristaService = new MotoristaService($motoristaRepository);

        $motoristas = [

            [
                'dados' => [
                    'usuario_id' => '1',
                    'cnh' => '132457689',
                    'data_validade_cnh' => '2030-12-31',
                    'ativo' => '1',
                ],
            ],

            [
                'dados' => [
                    'usuario_id' => '2',
                    'cnh' => '123456789',
                    'data_validade_cnh' => '2030-12-31',
                    'ativo' => '1',
                ],
            ],

             [
                'dados' => [
                    'usuario_id' => '3',
                    'cnh' => '987654321',
                    'data_validade_cnh' => '2030-12-31',
                    'ativo' => '1',
                ],
            ],
            
        ];

foreach ($motoristas as $item) {
            $usuarioId = (int) $item['dados']['usuario_id'];

            // 1. Verifica se o usuário existe
            $stmtUser = $pdo->prepare("SELECT id, nome FROM usuario WHERE id = ? LIMIT 1");
            $stmtUser->execute([$usuarioId]);
            $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                echo "[ERRO] Usuário ID {$usuarioId} não encontrado.\n";
                continue;
            }

            // 2. Garante o cargo 'Motorista' (papel_id = 2) na tabela usuario_papeis
            $stmtPapel = $pdo->prepare("
                SELECT id FROM usuario_papeis 
                WHERE usuario_id = ? AND papel_id = 2 AND ativo = 1 
                LIMIT 1
            ");
            $stmtPapel->execute([$usuarioId]);

            if (!$stmtPapel->fetchColumn()) {
                $insPapel = $pdo->prepare("
                    INSERT INTO usuario_papeis (usuario_id, papel_id, data_hora_inicio, ativo) 
                    VALUES (?, 2, NOW(), 1)
                ");
                $insPapel->execute([$usuarioId]);
                echo "[OK] Cargo 'Motorista' adicionado ao usuário {$usuario['nome']}.\n";
            }

            // 3. Verifica se já possui cadastro na tabela motorista
            $stmtMot = $pdo->prepare("SELECT id FROM motorista WHERE usuario_id = ? OR cnh = ? LIMIT 1");
            $stmtMot->execute([$usuarioId, $item['dados']['cnh']]);

            if ($stmtMot->fetchColumn()) {
                echo "[IGNORADO] Motorista já cadastrado para o usuário ID {$usuarioId}.\n";
                continue;
            }

            // 4. Cadastra os dados de CNH na tabela motorista (apenas 1 parâmetro)
            $motoristaService->cadastrar($item['dados']);
            echo "[OK] Motorista cadastrado: {$usuario['nome']} (ID {$usuarioId})\n";
        }
    }
}

(new MotoristaSeed())->run();