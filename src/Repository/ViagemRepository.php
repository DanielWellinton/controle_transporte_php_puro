<?php

class ViagemRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function paginate(int $limite = 10, int $pagina = 1, ?string $search = null, ?string $dataInicio = null, ?string $dataFim = null, ?string $status = null): array
    {
        $offset = ($pagina - 1) * $limite;
        $whereClauses = [];
        $params = [];

        // Busca textual geral (Rota, Motorista/Usuário ou Veículo/Placa)
        if (!empty($search)) {
            $whereClauses[] = "(r.descricao LIKE :search OR u.nome LIKE :search OR v.descricao LIKE :search OR v.placa LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        // Filtros por intervalo de datas de saída
        if (!empty($dataInicio)) {
            $whereClauses[] = "viagem.data_hora_saida >= :data_inicio";
            $params['data_inicio'] = $dataInicio . ' 00:00:00';
        }

        if (!empty($dataFim)) {
            $whereClauses[] = "viagem.data_hora_saida <= :data_fim";
            $params['data_fim'] = $dataFim . ' 23:59:59';
        }

        // Filtro por Status
        if ($status !== null && $status !== '') {
            $whereClauses[] = "viagem.ativo = :ativo";
            $params['ativo'] = (int) $status;
        }

        $whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

        // Query Base com Joins para abranger todas as relações
        $sqlBase = "FROM viagem viagem
                    JOIN rota r ON viagem.rota_id = r.id
                    JOIN motorista m ON viagem.motorista_id = m.id
                    JOIN usuario u ON m.usuario_id = u.id
                    JOIN veiculo v ON viagem.veiculo_id = v.id
                    {$whereSql}";

        // Contagem total para paginação
        $sqlCount = "SELECT COUNT(viagem.id) {$sqlBase}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Busca paginada com dados completos
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       u.nome as motorista_nome, 
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

        return [
            'data' => $viagens,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT viagem.*, 
                       r.descricao as rota_descricao, 
                       u.nome as motorista_nome, 
                       v.descricao as veiculo_descricao, 
                       v.placa as veiculo_placa,
                       v.numero_passageiros as veiculo_capacidade
                FROM viagem viagem
                JOIN rota r ON viagem.rota_id = r.id
                JOIN motorista m ON viagem.motorista_id = m.id
                JOIN usuario u ON m.usuario_id = u.id
                JOIN veiculo v ON viagem.veiculo_id = v.id
                WHERE viagem.id = :id 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $viagem = $stmt->fetch(PDO::FETCH_ASSOC);

        return $viagem ?: null;
    }

    public function create(array $dados): int
    {
        $codigoQr = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $sql = "INSERT INTO viagem (rota_id, motorista_id, veiculo_id, data_hora_saida, data_hora_chegada, codigo_qr, ativo, criado_at, atualizado_at) 
                VALUES (:rota_id, :motorista_id, :veiculo_id, :data_hora_saida, :data_hora_chegada, :codigo_qr, :ativo, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'rota_id'           => $dados['rota_id'],
            'motorista_id'      => $dados['motorista_id'],
            'veiculo_id'        => $dados['veiculo_id'],
            'data_hora_saida'   => $dados['data_hora_saida'],
            'data_hora_chegada' => !empty($dados['data_hora_chegada']) ? $dados['data_hora_chegada'] : null,
            'codigo_qr'         => $codigoQr,
            'ativo'             => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE viagem 
                SET rota_id = :rota_id, 
                    motorista_id = :motorista_id, 
                    veiculo_id = :veiculo_id, 
                    data_hora_saida = :data_hora_saida, 
                    data_hora_chegada = :data_hora_chegada, 
                    ativo = :ativo, 
                    atualizado_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id'                => $id,
            'rota_id'           => $dados['rota_id'],
            'motorista_id'      => $dados['motorista_id'],
            'veiculo_id'        => $dados['veiculo_id'],
            'data_hora_saida'   => $dados['data_hora_saida'],
            'data_hora_chegada' => !empty($dados['data_hora_chegada']) ? $dados['data_hora_chegada'] : null,
            'ativo'             => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM viagem WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // Métodos auxiliares para popular selects nos formulários
    public function getRotasAtivas(): array
    {
        $stmt = $this->pdo->query("SELECT id, descricao FROM rota WHERE ativo = 1 ORDER BY descricao ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMotoristasAtivos(): array
    {
        $sql = "SELECT m.id, u.nome as descricao 
                FROM motorista m 
                JOIN usuario u ON m.usuario_id = u.id 
                WHERE m.ativo = 1 
                ORDER BY u.nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVeiculosAtivos(): array
    {
        $sql = "SELECT id, CONCAT(descricao, ' - Placa: ', placa) as descricao 
                FROM veiculo 
                WHERE ativo = 1 
                ORDER BY descricao ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}