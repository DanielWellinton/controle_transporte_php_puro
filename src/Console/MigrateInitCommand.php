<?php

class MigrateInitCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'migrate:init';
    }

    public function execute(array $args): void
    {
        try {
            $pdo = Database::conectar();
            $migrationRepository = new MigracaoRepository($pdo);
            $migrationRepository->criarTabelaSeNaoExistir();
            echo "\033[32m[SUCESSO] Criada tabela migracao!\033[0m\n";
        } catch (\Exception $e) {
            echo "\033[31m[ERRO] Falha na criação da tabela migracao: " . $e->getMessage() . "\033[0m\n";
        }
    }
}