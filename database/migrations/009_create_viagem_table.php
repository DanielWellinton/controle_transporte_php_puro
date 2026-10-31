<?php

class CreateViagemTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS viagem (
            id INT AUTO_INCREMENT PRIMARY KEY,
            qrcode VARCHAR(255) NOT NULL UNIQUE,
            data_hora_saida DATETIME NOT NULL,
            data_hora_chegada DATETIME NOT NULL,
            rota_id INT NOT NULL,
            motorista_id INT NOT NULL,
            veiculo_id INT NOT NULL,
            ativo BOOLEAN NOT NULL DEFAULT TRUE,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_viagem_rota FOREIGN KEY (rota_id) REFERENCES rota(id) ON DELETE CASCADE,
            CONSTRAINT fk_viagem_motorista FOREIGN KEY (motorista_id) REFERENCES motorista(id) ON DELETE CASCADE,
            CONSTRAINT fk_viagem_veiculo FOREIGN KEY (veiculo_id) REFERENCES veiculo(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }
}
