<?php

class PortalMotoristaRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retorna o registo de motorista associado ao ID do usuário.
     */
    public function findMotoristaByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM motorista WHERE usuario_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $motorista = $stmt->fetch(PDO::FETCH_ASSOC);
        return $motorista ?: null;
    }

    /**
     * Lista viagens ativas do motorista com rota e veículo.
     */
    public function getViagensAtivasByMotorista(int $motoristaId): array
    {
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa
                FROM viagem viagem
                JOIN rota r ON viagem.rota_id = r.id
                JOIN veiculo v ON viagem.veiculo_id = v.id
                WHERE viagem.motorista_id = :motorista_id 
                  AND viagem.ativo = 1
                ORDER BY viagem.data_hora_saida ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['motorista_id' => $motoristaId]);
        $viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Carrega pontos de parada e passageiros para cada viagem
        foreach ($viagens as &$viagem) {
            $viagem['pontos_de_parada'] = $this->getPontosParadaByRota((int) $viagem['rota_id']);
            $viagem['passageiros'] = $this->getPassageirosByViagem((int) $viagem['id']);
        }

        return $viagens;
    }

    /**
     * Paginação e filtros para o histórico de viagens finalizadas/inativas.
     */
    public function paginateHistorico(int $motoristaId, int $limite = 10, int $pagina = 1, ?string $search = null, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $offset = ($pagina - 1) * $limite;
        $whereClauses = ["viagem.motorista_id = :motorista_id", "viagem.ativo = 0"];
        $params = ['motorista_id' => $motoristaId];

        if (!empty($search)) {
            $whereClauses[] = "(r.descricao LIKE :search OR v.descricao LIKE :search OR v.placa LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if (!empty($dataInicio)) {
            $whereClauses[] = "viagem.data_hora_saida >= :data_inicio";
            $params['data_inicio'] = $dataInicio . ' 00:00:00';
        }

        if (!empty($dataFim)) {
            $whereClauses[] = "viagem.data_hora_saida <= :data_fim";
            $params['data_fim'] = $dataFim . ' 23:59:59';
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        $sqlBase = "FROM viagem viagem
                    JOIN rota r ON viagem.rota_id = r.id
                    JOIN veiculo v ON viagem.veiculo_id = v.id
                    {$whereSql}";

        // Total para paginação
        $stmtCount = $this->pdo->prepare("SELECT COUNT(viagem.id) {$sqlBase}");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Busca paginada com contagem de passageiros embarcados
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa,
                       (SELECT COUNT(*) FROM passageiros p WHERE p.viagem_id = viagem.id AND p.data_hora_saida IS NOT NULL) as embarcados_count
                {$sqlBase}
                ORDER BY viagem.data_hora_chegada DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $historico,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    /**
     * Encontra uma viagem específica pelo ID com detalhes completos.
     */
    public function findViagemDetalhes(int $viagemId): ?array
    {
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       m.id as motorista_id_rel,
                       u.name as motorista_nome,
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa
                FROM viagem viagem
                JOIN rota r ON viagem.rota_id = r.id
                JOIN motorista m ON viagem.motorista_id = m.id
                JOIN usuario u ON m.usuario_id = u.id
                JOIN veiculo v ON viagem.veiculo_id = v.id
                WHERE viagem.id = :id 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $viagemId]);
        $viagem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$viagem) {
            return null;
        }

        $viagem['pontos_de_parada'] = $this->getPontosParadaByRota((int) $viagem['rota_id']);
        $viagem['passageiros'] = $this->getPassageirosByViagem($viagemId);

        return $viagem;
    }

    public function iniciarViagem(int $viagemId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE viagem SET data_hora_saida = NOW(), atualizado_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $viagemId]);
    }

    public function finalizarViagem(int $viagemId): void
    {
        $this->pdo->beginTransaction();

        try {
            // 1. Inativa e define a hora de encerramento da viagem
            $stmtViagem = $this->pdo->prepare("UPDATE viagem SET data_hora_chegada = NOW(), ativo = 0, atualizado_at = NOW() WHERE id = :id");
            $stmtViagem->execute(['id' => $viagemId]);

            // 2. Registra o desembarque apenas de quem embarcou e não desembarcou
            $stmtPassageiros = $this->pdo->prepare("UPDATE passageiros 
                                                    SET data_hora_chegada = NOW(), updated_at = NOW() 
                                                    WHERE viagem_id = :viagem_id 
                                                      AND data_hora_saida IS NOT NULL 
                                                      AND data_hora_chegada IS NULL");
            $stmtPassageiros->execute(['viagem_id' => $viagemId]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function getPontosParadaByRota(int $rotaId): array
    {
        $sql = "SELECT p.*, rpp.ordem, rpp.ativo as pivot_ativo 
                FROM ponto_de_parada p 
                JOIN rota_ponto_de_parada rpp ON p.id = rpp.ponto_de_parada_id 
                WHERE rpp.rota_id = :rota_id 
                ORDER BY rpp.ordem ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rota_id' => $rotaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPassageirosByViagem(int $viagemId): array
    {
        $sql = "SELECT u.*, p.data_hora_saida as pivot_data_hora_saida, p.data_hora_chegada as pivot_data_hora_chegada 
                FROM passageiros p 
                JOIN usuario u ON p.usuario_id = u.id 
                WHERE p.viagem_id = :viagem_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['viagem_id' => $viagemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}