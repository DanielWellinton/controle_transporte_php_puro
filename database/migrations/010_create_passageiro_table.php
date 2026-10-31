<?php

class CreatePassageiroTable
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS passageiro (
            id INT AUTO_INCREMENT PRIMARY KEY,
            viagem_id INT NOT NULL,
            usuario_id INT NOT NULL,
            ponto_de_parada_saida_id INT NOT NULL,
            ponto_de_parada_chegada_id INT NOT NULL,
            data_hora_saida DATETIME NOT NULL,
            data_hora_chegada DATETIME NOT NULL,
            criado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_passageiro_viagem FOREIGN KEY (viagem_id) REFERENCES viagem(id) ON DELETE CASCADE,
            CONSTRAINT fk_passageiro_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
            CONSTRAINT fk_passageiro_ponto_saida FOREIGN KEY (ponto_de_parada_saida_id) REFERENCES rota_ponto_de_parada(id) ON DELETE CASCADE,
            CONSTRAINT fk_passageiro_ponto_chegada FOREIGN KEY (ponto_de_parada_chegada_id) REFERENCES rota_ponto_de_parada(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }
}
