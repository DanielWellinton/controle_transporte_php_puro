<?php
// Carrega o autoload do Composer para resolver os namespaces automaticamente
require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "<h3>Iniciando processo de banco de dados...</h3><br>";

    // 1. Executa o migrate:init para criar a tabela de controle
    $_SERVER['argv'] = ['bin/console', 'migrate:init'];
    require __DIR__ . '/bin/console';
    echo "<br>---<br>";

    // 2. Executa o migrate para aplicar as migrações
    $_SERVER['argv'] = ['bin/console', 'migrate'];
    require __DIR__ . '/bin/console';

    echo "<br><br><b>Processo de migração finalizado com sucesso!</b>";
} catch (\Exception $e) {
    echo "<br><span style='color:red;'>Erro ao rodar migrações: " . htmlspecialchars($e->getMessage()) . "</span>";
}