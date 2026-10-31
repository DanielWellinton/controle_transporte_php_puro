<?php

class MigrarCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'migrate';
    }

    public function execute(array $args): void
    {
        try {
            $pdo = Database::conectar();
            $migrationRepository = new MigracaoRepository($pdo);

            $pastaMigrations = __DIR__ . '/../../database/migrations';
            if (!is_dir($pastaMigrations)) {
                echo "\033[33m[AVISO] A pasta database/migrations não existe.\033[0m\n";
                return;
            }

            $arquivos = glob($pastaMigrations . '/*.php');
            sort($arquivos); // Ordem sequencial

            if (empty($arquivos)) {
                echo "\033[33m[AVISO] Nenhuma migração PHP encontrada.\033[0m\n";
                return;
            }

            foreach ($arquivos as $arquivo) {
                $nomeArquivo = basename($arquivo, '.php');

                // Verifica no banco se já foi executada
                $stmtCheck = $pdo->prepare("SELECT ativo FROM migracao WHERE descricao = :desc");
                $stmtCheck->execute(['desc' => $nomeArquivo]);
                $statusBanco = $stmtCheck->fetchColumn();

                if ($statusBanco == 1) {
                    echo "\033[36m[IGNORADA]\033[0m {$nomeArquivo} já foi executada.\n";
                    continue;
                }

                // Inclui o arquivo da migração PHP
                require_once $arquivo;

                // Descobre o nome da classe com base no nome do arquivo (ex: 001_create_usuario_table -> _001CreateUsuarioTable ou similar)
                // Vamos extrair a parte limpa do nome da classe gerada
                $partes = explode('_', $nomeArquivo);
                array_shift($partes); // remove o número (ex: 001)
                $nomeClasse = str_replace(' ', '', ucwords(implode(' ', $partes)));

                if (!class_exists($nomeClasse)) {
                    echo "\033[31m[ERRO] A classe '{$nomeClasse}' não foi encontrada no arquivo {$nomeArquivo}.php\033[0m\n";
                    continue;
                }

                // Registra a migração como pendente no banco
                $migracao = $migrationRepository->create($nomeArquivo, false);

                // Instancia e executa o método up passando o PDO
                $migracaoObj = new $nomeClasse();
                if (method_exists($migracaoObj, 'up')) {
                    $migracaoObj->up($pdo);
                }

                // Atualiza o status para ativo = true
                $migracao->setAtivo(true);
                $migrationRepository->update($migracao);

                echo "\033[32m[SUCESSO] Migração PHP executada: {$nomeArquivo}\033[0m\n";
            }

        } catch (\Exception $e) {
            echo "\033[31m[ERRO] Falha na migração: " . $e->getMessage() . "\033[0m\n";
        }
    }
}