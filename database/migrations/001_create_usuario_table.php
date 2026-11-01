<?php

class CreateUsuarioTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS usuario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            salt VARCHAR(255) NOT NULL,
            telefone VARCHAR(20) DEFAULT NULL,
            data_nascimento DATE NOT NULL,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql);
    }
}