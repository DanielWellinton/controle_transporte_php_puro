<?php

class PassageiroRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lista viagens ativas com filtros para a vitrine/dashboard do passageiro.
     */
    public function paginateDashboard(int $userId, int $limite = 9, int $pagina = 1, ?string $search = null, ?string $dataInicio = null, ?string $dataFim = null, ?string $inscricao = null): array
    {
        $offset = ($pagina - 1) * $limite;
        $whereClauses = ["viagem.ativo = 1"];
        $params = [];

        if (!empty($search)) {
            $whereClauses[] = "(r.descricao LIKE :search OR u_mot.nome LIKE :search OR v.descricao LIKE :search OR v.placa LIKE :search)";
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

        if ($inscricao === 'inscrito') {
            $whereClauses[] = "EXISTS (SELECT 1 FROM passageiro p_sub WHERE p_sub.viagem_id = viagem.id AND p_sub.usuario_id = :user_id_inscrito)";
            $params['user_id_inscrito'] = $userId;
        } elseif ($inscricao === 'nao_inscrito') {
            $whereClauses[] = "NOT EXISTS (SELECT 1 FROM passageiro p_sub WHERE p_sub.viagem_id = viagem.id AND p_sub.usuario_id = :user_id_nao_inscrito)";
            $params['user_id_nao_inscrito'] = $userId;
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        $sqlBase = "FROM viagem viagem
                    JOIN rota r ON viagem.rota_id = r.id
                    JOIN motorista m ON viagem.motorista_id = m.id
                    JOIN usuario u_mot ON m.usuario_id = u_mot.id
                    JOIN veiculo v ON viagem.veiculo_id = v.id
                    {$whereSql}";

        // Contagem total para paginação
        $stmtCount = $this->pdo->prepare("SELECT COUNT(viagem.id) {$sqlBase}");
        foreach ($params as $key => $val) {
            $stmtCount->bindValue(":{$key}", $val);
        }
        $stmtCount->execute();
        $total = (int) $stmtCount->fetchColumn();

        // Busca paginada
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       u_mot.nome as motorista_nome, 
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa
                {$sqlBase}
                ORDER BY viagem.data_hora_saida ASC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Associa pontos, veículo, motorista e passageiros inscritos a cada viagem
        foreach ($viagens as &$viagem) {
            $viagem['pontos_de_parada'] = $this->getPontosParadaByRota((int) $viagem['rota_id']);
            $viagem['passageiros'] = $this->getPassageirosByViagem((int) $viagem['id']);
        }

        return [
            'data' => $viagens,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    /**
     * Encontra uma viagem específica com detalhes completos para seleção de pontos.
     */
    public function findViagemDetalhes(int $viagemId): ?array
    {
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       u_mot.nome as motorista_nome, 
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa
                FROM viagem viagem
                JOIN rota r ON viagem.rota_id = r.id
                JOIN motorista m ON viagem.motorista_id = m.id
                JOIN usuario u_mot ON m.usuario_id = u_mot.id
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

    /**
     * Retorna a inscrição do usuário numa viagem específica.
     */
    public function findInscricaoPassageiro(int $userId, int $viagemId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM passageiro WHERE usuario_id = :user_id AND viagem_id = :viagem_id LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'viagem_id' => $viagemId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Inscreve ou atualiza os pontos de embarque/desembarque do passageiro.
     */
    public function salvarPontosInscricao(int $userId, int $viagemId, int $pontoSaidaId, int $pontoChegadaId): void
    {
        $existente = $this->findInscricaoPassageiro($userId, $viagemId);

        if ($existente) {
            $stmt = $this->pdo->prepare("UPDATE passageiro 
                                         SET ponto_de_parada_saida_id = :saida, 
                                             ponto_de_parada_chegada_id = :chegada, 
                                             updated_at = NOW() 
                                         WHERE usuario_id = :user_id AND viagem_id = :viagem_id");
            $stmt->execute([
                'saida' => $pontoSaidaId,
                'chegada' => $pontoChegadaId,
                'user_id' => $userId,
                'viagem_id' => $viagemId
            ]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO passageiro (usuario_id, viagem_id, ponto_de_parada_saida_id, ponto_de_parada_chegada_id, data_hora_saida, created_at, updated_at) 
                                         VALUES (:user_id, :viagem_id, :saida, :chegada, NULL, NOW(), NOW())");
            $stmt->execute([
                'user_id' => $userId,
                'viagem_id' => $viagemId,
                'saida' => $pontoSaidaId,
                'chegada' => $pontoChegadaId
            ]);
        }
    }

    /**
     * Cancela a inscrição do passageiro na viagem.
     */
    public function cancelarInscricao(int $userId, int $viagemId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM passageiro WHERE usuario_id = :user_id AND viagem_id = :viagem_id");
        return $stmt->execute(['user_id' => $userId, 'viagem_id' => $viagemId]);
    }

    /**
     * Histórico de viagens do passageiro logado com paginação e filtros.
     */
    public function paginateHistorico(int $userId, int $limite = 10, int $pagina = 1, ?string $search = null, ?string $dataInicio = null, ?string $dataFim = null, ?string $statusEmbarque = null): array
    {
        $offset = ($pagina - 1) * $limite;
        $whereClauses = ["p_hist.usuario_id = :user_id"];
        $params = ['user_id' => $userId];

        if (!empty($search)) {
            $whereClauses[] = "(r.descricao LIKE :search OR u_mot.nome LIKE :search OR v.descricao LIKE :search OR v.placa LIKE :search)";
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

        if ($statusEmbarque === 'embarcado') {
            $whereClauses[] = "p_hist.data_hora_saida IS NOT NULL";
        } elseif ($statusEmbarque === 'pendente') {
            $whereClauses[] = "p_hist.data_hora_saida IS NULL";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        $sqlBase = "FROM viagem viagem
                    JOIN passageiro p_hist ON viagem.id = p_hist.viagem_id
                    JOIN rota r ON viagem.rota_id = r.id
                    JOIN motorista m ON viagem.motorista_id = m.id
                    JOIN usuario u_mot ON m.usuario_id = u_mot.id
                    JOIN veiculo v ON viagem.veiculo_id = v.id
                    {$whereSql}";

        // Contagem total
        $stmtCount = $this->pdo->prepare("SELECT COUNT(viagem.id) {$sqlBase}");
        foreach ($params as $key => $val) {
            $stmtCount->bindValue(":{$key}", $val);
        }
        $stmtCount->execute();
        $total = (int) $stmtCount->fetchColumn();

        // Busca paginada
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       u_mot.nome as motorista_nome, 
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa
                {$sqlBase}
                ORDER BY viagem.data_hora_saida DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Carrega passageiros e pontos específicos do usuário para o histórico
        foreach ($viagens as &$viagem) {
            $viagem['passageiros'] = $this->getPassageirosByViagem((int) $viagem['id']);
        }

        return [
            'data' => $viagens,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
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
        $sql = "SELECT u.*, 
                       p.ponto_de_parada_saida_id as pivot_ponto_de_parada_saida_id,
                       p.data_hora_saida as pivot_data_hora_saida, 
                       p.ponto_de_parada_chegada_id as pivot_ponto_de_parada_chegada_id,
                       p.data_hora_chegada as pivot_data_hora_chegada 
                FROM passageiro p 
                JOIN usuario u ON p.usuario_id = u.id 
                WHERE p.viagem_id = :viagem_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['viagem_id' => $viagemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDescricoesPontos(array $pontosIds): array
    {
        if (empty($pontosIds)) {
            return [];
        }
        $inQuery = implode(',', array_fill(0, count($pontosIds), '?'));
        $stmt = $this->pdo->prepare("SELECT id, descricao FROM ponto_de_parada WHERE id IN ({$inQuery})");
        $stmt->execute(array_values($pontosIds));
        $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [id => descricao]
        return $result;
    }
}