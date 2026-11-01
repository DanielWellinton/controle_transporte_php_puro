<?php

class MotoristaRepository
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
            $whereClauses[] = "(m.cnh LIKE :search OR u.nome LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== null && $status !== '') {
            $whereClauses[] = "m.ativo = :status";
            $params['status'] = (int) $status;
        }

        $whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

        // Conta o total de registos para a paginação
        $sqlCount = "SELECT COUNT(*) FROM motorista m JOIN usuario u ON m.usuario_id = u.id {$whereSql}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Busca os registos paginados com JOIN na tabela de usuários
        $sql = "SELECT m.*, u.nome as usuario_nome, u.email as usuario_email 
                FROM motorista m 
                JOIN usuario u ON m.usuario_id = u.id 
                {$whereSql} 
                ORDER BY m.id DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT m.*, u.nome as usuario_nome, u.email as usuario_email 
                FROM motorista m 
                JOIN usuario u ON m.usuario_id = u.id 
                WHERE m.id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    public function create(array $dados): bool
    {
        $sql = "INSERT INTO motorista (cnh, data_validade_cnh, ativo, usuario_id) 
                VALUES (:cnh, :data_validade_cnh, :ativo, :usuario_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'cnh' => $dados['cnh'],
            'data_validade_cnh' => $dados['data_validade_cnh'],
            'ativo' => isset($dados['ativo']) ? 1 : 0,
            'usuario_id' => $dados['usuario_id']
        ]);
    }

    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE motorista 
                SET cnh = :cnh, 
                    data_validade_cnh = :data_validade_cnh, 
                    ativo = :ativo, 
                    usuario_id = :usuario_id 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'cnh' => $dados['cnh'],
            'data_validade_cnh' => $dados['data_validade_cnh'],
            'ativo' => isset($dados['ativo']) ? 1 : 0,
            'usuario_id' => $dados['usuario_id']
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM motorista WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function autocomplete(string $term, ?int $currentMotoristaId = null): array
    {
        $sql = "SELECT m.id, u.nome, u.email FROM motorista m
                JOIN usuario u ON m.usuario_id = u.id
                WHERE (m.ativo = 1 " . ($currentMotoristaId ? "OR m.id = :current_id" : "") . ")
                  AND (u.nome LIKE :term OR m.id = :term_id)
                LIMIT 8";

        $stmt = $this->pdo->prepare($sql);
        if ($currentMotoristaId) {
            $stmt->bindValue(':current_id', $currentMotoristaId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':term', "%{$term}%");
        $stmt->bindValue(':term_id', $term);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}