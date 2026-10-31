<?php

class CreateVeiculoTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS veiculo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            descricao VARCHAR(150) NOT NULL,
            placa VARCHAR(10) NOT NULL UNIQUE,
            numero_passageiros INT NOT NULL,
            ativo BOOLEAN NOT NULL DEFAULT TRUE,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }
}
