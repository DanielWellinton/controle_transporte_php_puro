<?php

class RotaRepository
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

        // Contagem total
        $sqlCount = "SELECT COUNT(*) FROM rota {$whereSql}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Busca paginada
        $sql = "SELECT * FROM rota {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $rotas,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM rota WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rota = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rota ?: null;
    }

    public function create(array $dados): int
    {
        $sql = "INSERT INTO rota (descricao, ativo, criado_at, atualizado_at) 
                VALUES (:descricao, :ativo, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'descricao' => $dados['descricao'],
            'ativo' => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE rota 
                SET descricao = :descricao, 
                    ativo = :ativo, 
                    atualizado_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'descricao' => $dados['descricao'],
            'ativo' => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);
    }

    public function delete(int $id): bool
    {
        // Remove os vínculos da pivot primeiro
        $stmtPivot = $this->pdo->prepare("DELETE FROM rota_ponto_de_parada WHERE rota_id = :id");
        $stmtPivot->execute(['id' => $id]);

        $sql = "DELETE FROM rota WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // --- Gestão da Pivot (Rota <-> Ponto de Parada) ---

    public function getPontosVinculados(int $rotaId): array
    {
        $sql = "SELECT p.*, rpp.ordem, rpp.ativo as pivot_ativo, rpp.ponto_de_parada_id 
                FROM ponto_de_parada p
                JOIN rota_ponto_de_parada rpp ON p.id = rpp.ponto_de_parada_id
                WHERE rpp.rota_id = :rota_id
                ORDER BY rpp.ordem ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rota_id' => $rotaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPontosDisponiveis(int $rotaId): array
    {
        $sql = "SELECT * FROM ponto_de_parada 
                WHERE ativo = 1 
                  AND id NOT IN (SELECT ponto_de_parada_id FROM rota_ponto_de_parada WHERE rota_id = :rota_id)
                ORDER BY descricao ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rota_id' => $rotaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function vincularPonto(int $rotaId, int $pontoId): bool
    {
        // Descobre a próxima ordem
        $sqlMax = "SELECT MAX(ordem) FROM rota_ponto_de_parada WHERE rota_id = :rota_id";
        $stmtMax = $this->pdo->prepare($sqlMax);
        $stmtMax->execute(['rota_id' => $rotaId]);
        $proximaOrdem = ((int) $stmtMax->fetchColumn()) + 1;

        $sql = "INSERT INTO rota_ponto_de_parada (rota_id, ponto_de_parada_id, ordem, ativo) 
                VALUES (:rota_id, :ponto_id, :ordem, 1)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'rota_id' => $rotaId,
            'ponto_id' => $pontoId,
            'ordem' => $proximaOrdem
        ]);
    }

    public function desvincularPonto(int $rotaId, int $pontoId): bool
    {
        $sql = "DELETE FROM rota_ponto_de_parada WHERE rota_id = :rota_id AND ponto_de_parada_id = :ponto_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'rota_id' => $rotaId,
            'ponto_id' => $pontoId
        ]);
    }

    public function atualizarPontosPivot(int $rotaId, array $pontos): void
    {
        $sql = "UPDATE rota_ponto_de_parada 
                SET ordem = :ordem, ativo = :ativo 
                WHERE rota_id = :rota_id AND ponto_de_parada_id = :ponto_id";
        $stmt = $this->pdo->prepare($sql);

        foreach ($pontos as $pontoId => $dados) {
            $stmt->execute([
                'rota_id' => $rotaId,
                'ponto_id' => (int) $pontoId,
                'ordem' => (int) $dados['ordem'],
                'ativo' => isset($dados['ativo']) ? 1 : 0
            ]);
        }
    }

    public function autocomplete(string $term, ?int $currentRotaId = null): array
    {
        $sql = "SELECT id, descricao FROM rota
                WHERE (ativo = 1 " . ($currentRotaId ? "OR id = :current_id" : "") . ")
                  AND descricao LIKE :term
                LIMIT 8";

        $stmt = $this->pdo->prepare($sql);
        if ($currentRotaId) {
            $stmt->bindValue(':current_id', $currentRotaId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}