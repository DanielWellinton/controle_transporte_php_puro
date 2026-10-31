<?php

class CreateRotaPontodeparadaTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS rota_ponto_de_parada (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rota_id INT NOT NULL,
            ponto_de_parada_id INT NOT NULL,
            ordem INT NOT NULL,
            ativo BOOLEAN NOT NULL DEFAULT TRUE,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_rota_ponto_rota FOREIGN KEY (rota_id) REFERENCES rota(id) ON DELETE CASCADE,
            CONSTRAINT fk_rota_ponto_parada FOREIGN KEY (ponto_de_parada_id) REFERENCES ponto_de_parada(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }
}
