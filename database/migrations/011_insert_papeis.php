<?php

class InsertPapeis
{
    public function up(PDO $pdo): void
    {
        $sql = "INSERT INTO controle_estoque_php_puro.papel
                (descricao, ativo, criado_at, atualizado_at) VALUES
                ('Admin', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
                ('Motorista', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
                ('Passageiro', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $pdo->exec($sql);
    }
}
