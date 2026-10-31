<?php

class CreateMotoristaTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS motorista (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cnh VARCHAR(20) NOT NULL UNIQUE,
            data_validade_cnh DATE NOT NULL,
            ativo BOOLEAN NOT NULL DEFAULT TRUE,
            usuario_id INT NOT NULL,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_motorista_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }
}
