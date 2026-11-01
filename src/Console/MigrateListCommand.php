<?php

class MigrateListCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'migrate:list';
    }

    public function execute(array $args): void
    {
        try {
            $pdo = Database::conectar();

            $pastaMigrations = __DIR__ . '/../../database/migrations';
            if (!is_dir($pastaMigrations)) {
                echo "\033[33m[AVISO] A pasta database/migrations não existe.\033[0m\n";
                return;
            }

            // Agora buscamos arquivos .php
            $arquivos = glob($pastaMigrations . '/*.php');
            sort($arquivos);

            // Busca migrações já registradas no banco [descricao => ativo]
            $stmt = $pdo->query("SELECT descricao, ativo FROM migracao");
            $registrosBanco = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            echo "\n\033[36m--- LISTA DE MIGRAÇÕES PHP ---\033[0m\n";
            if (empty($arquivos)) {
                echo "Nenhuma migração PHP encontrada na pasta.\n";
                return;
            }

            foreach ($arquivos as $arquivo) {
                $nomeArquivo = basename($arquivo, '.php');
                $executada = isset($registrosBanco[$nomeArquivo]);
                $ativa = $executada && $registrosBanco[$nomeArquivo] == 1;

                if ($ativa) {
                    $status = "\033[32m[EXECUTADA]\033[0m";
                } else {
                    $status = "\033[33m[PENDENTE]\033[0m";
                }

                echo " {$status} {$nomeArquivo}\n";
            }
            echo "\n";

        } catch (\Exception $e) {
            echo "\033[31m[ERRO] " . $e->getMessage() . "\033[0m\n";
        }
    }
}