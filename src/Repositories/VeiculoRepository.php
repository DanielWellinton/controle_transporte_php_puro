<?php

class VeiculoRepository
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
            $whereClauses[] = "(descricao LIKE :search OR placa LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== null && $status !== '') {
            $whereClauses[] = "ativo = :ativo";
            $params['ativo'] = (int) $status;
        }

        $whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

        // Conta o total de registros para a paginação
        $sqlCount = "SELECT COUNT(*) FROM veiculo {$whereSql}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Busca os veículos paginados
        $sql = "SELECT * FROM veiculo {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $veiculos,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM veiculo WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $veiculo ?: null;
    }

    public function create(array $dados): bool
    {
        $sql = "INSERT INTO veiculo (descricao, placa, numero_passageiros, ativo, criado_at, atualizado_at) 
                VALUES (:descricao, :placa, :numero_passageiros, :ativo, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'descricao' => $dados['descricao'],
            'placa' => strtoupper($dados['placa']),
            'numero_passageiros' => (int) $dados['numero_passageiros'],
            'ativo' => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);
    }

    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE veiculo 
                SET descricao = :descricao, 
                    placa = :placa, 
                    numero_passageiros = :numero_passageiros,
                    ativo = :ativo, 
                    atualizado_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'descricao' => $dados['descricao'],
            'placa' => strtoupper($dados['placa']),
            'numero_passageiros' => (int) $dados['numero_passageiros'],
            'ativo' => isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM veiculo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function autocomplete(string $term, ?int $currentVeiculoId = null): array
    {
        // Equivalente à query do Eloquent com tratamento seguro para evitar duplicações de parâmetros
        $sql = "SELECT id, descricao, placa FROM veiculo
                WHERE (ativo = 1 " . ($currentVeiculoId ? "OR id = :current_id" : "") . ")
                  AND (descricao LIKE :term OR placa LIKE :term)
                LIMIT 8";

        $stmt = $this->pdo->prepare($sql);
        if ($currentVeiculoId) {
            $stmt->bindValue(':current_id', $currentVeiculoId, PDO::PARAM_INT);
        }
        
        $stmt->bindValue(':term', "%{$term}%");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}