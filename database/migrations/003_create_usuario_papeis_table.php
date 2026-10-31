<?php

class CreateUsuarioPapeisTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS usuario_papeis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            data_hora_inicio DATETIME NOT NULL,
            data_hora_fim DATETIME DEFAULT NULL,
            ativo BOOLEAN NOT NULL DEFAULT TRUE,
            usuario_id INT NOT NULL,
            papel_id INT NOT NULL,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_usuario_papeis_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
            CONSTRAINT fk_usuario_papeis_papel FOREIGN KEY (papel_id) REFERENCES papel(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }
}
