<?php

class PontoDeParadaRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function paginate(int $limite = 10, int $pagina = 1, ?string $search = null, ?string $status = null): array
    {
        $offset = ($pagina - 1) * $limite;
        $whereClauses = [];
        $params = [];

        if (!empty($search)) {
            $whereClauses[] = "descricao LIKE :search";
            $params['search'] = "%{$search}%";
        }

        if ($status !== null && $status !== '') {
            $whereClauses[] = "ativo = :ativo";
            $params['ativo'] = (int) $status;
        }

        $whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

        // Contagem total para paginação
        $sqlCount = "SELECT COUNT(*) FROM ponto_de_parada {$whereSql}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Busca paginada
        $sql = "SELECT * FROM ponto_de_parada {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $pontos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $pontos,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM ponto_de_parada WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $ponto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $ponto ?: null;
    }

    public function create(array $dados): bool
    {
        $sql = "INSERT INTO ponto_de_parada (descricao, latitude, longitude, ativo, criado_at, atualizado_at) 
                VALUES (:descricao, :latitude, :longitude, :ativo, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'descricao' => $dados['descricao'],
            'latitude'  => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'ativo'     => isset($dados['ativo']) ? (int) $dados['ativo' ] : 1
        ]);
    }

    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE ponto_de_parada 
                SET descricao = :descricao, 
                    latitude = :latitude,
                    longitude = :longitude,
                    ativo = :ativo, 
                    atualizado_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id'        => $id,
            'descricao' => $dados['descricao'],
            'latitude'  => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'ativo'     => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM ponto_de_parada WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function autocomplete(string $term, ?int $rotaId = null): array
    {
        $whereClauses = ["descricao LIKE :term"];
        $params = ['term' => '%' . $term . '%'];

        if ($rotaId) {
            $whereClauses[] = "id NOT IN (SELECT ponto_de_parada_id FROM rota_ponto_de_parada WHERE rota_id = :rota_id)";
            $params['rota_id'] = $rotaId;
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        $sql = "SELECT id, descricao FROM ponto_de_parada 
                {$whereSql} 
                ORDER BY descricao ASC 
                LIMIT 10";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":{$key}", $val, $type);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}