<?php

class MigrateMakeCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'migrate:make';
    }

    public function execute(array $args): void
    {
        $nome = $args[0] ?? null;
        if (!$nome) {
            echo "\033[31m[ERRO] Informe o nome da migração. Ex: php bin/console migrate:make create_usuario_table\033[0m\n";
            return;
        }

        $pastaMigrations = __DIR__ . '/../../database/migrations';
        if (!is_dir($pastaMigrations)) {
            mkdir($pastaMigrations, 0777, true);
        }

        // Descobre o próximo número sequencial
        $arquivos = glob($pastaMigrations . '/*.php');
        $proximoNumero = count($arquivos) + 1;
        $prefixo = str_pad($proximoNumero, 3, '0', STR_PAD_LEFT);

        // Nome da classe gerada baseada no nome (ex: CreateUsuarioTable)
        $nomeClasse = str_replace(' ', '', ucwords(str_replace('_', ' ', $nome)));
        $nomeArquivo = "{$prefixo}_{$nome}.php";
        $caminhoCompleto = "{$pastaMigrations}/{$nomeArquivo}";

        $conteudoPhp = "<?php\n\nclass {$nomeClasse}\n{\n    public function up(PDO \$pdo): void\n    ---- (substitua os traços por chaves) ----\n        // Escreva seus comandos SQL ou PDO aqui\n        \$pdo->exec(\"\"\"\n            CREATE TABLE IF NOT EXISTS exemplo (\n                id INT AUTO_INCREMENT PRIMARY KEY\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n        \"\"\");\n    ---- (substitua os traços por chaves) ----\n}\n";
        
        // Ajustando as chaves para evitar conflitos de template
        $conteudoPhp = str_replace('---- (substitua os traços por chaves) ----', '{', $conteudoPhp);
        // Pequeno ajuste para fechar a classe corretamente:
        $conteudoPhp = "<?php\n\nclass {$nomeClasse}\n{\n    public function up(PDO \$pdo): void\n    {\n        // Escreva seus comandos PDO aqui\n        /*\n        \$pdo->exec(\"CREATE TABLE ...\");\n        */\n    }\n}\n";

        file_put_contents($caminhoCompleto, $conteudoPhp);

        echo "\033[32m[SUCESSO] Migração PHP criada: database/migrations/{$nomeArquivo}\033[0m\n";
    }
}