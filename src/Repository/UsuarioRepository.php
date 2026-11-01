<?php

class UsuarioRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo =$pdo;
    }

    public function paginate(int $limite = 10, int$pagina = 1, ?string $search = null, ?string $papelId = null): array
    {
        $offset = ($pagina - 1) *$limite;
        $whereClauses = [];$params = [];

        if (!empty($search)) {$whereClauses[] = "(u.nome LIKE :search OR u.email LIKE :search)";
            $params['search'] = "\%{$search}%";
        }

        if (!empty($papelId)) {$whereClauses[] = "EXISTS (SELECT 1 FROM usuario_papeis up WHERE up.usuario_id = u.id AND up.papel_id = :papel_id AND up.ativo = 1)";
            $params['papel_id'] = (int)$papelId;
        }

        $whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

        // Conta o total de registros para paginação
        $sqlCount = "SELECT COUNT(*) FROM usuario u {$whereSql}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Busca os usuários paginados
        $sql = "SELECT u.* FROM usuario u {$whereSql} ORDER BY u.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as$key => $val) {$stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);$stmt->execute();
        $usuarios =$stmt->fetchAll(PDO::FETCH_ASSOC);

        // Carrega os papéis ativos de cada usuário para exibir na view
        foreach ($usuarios as &$usuario) {$usuario['papeis'] = $this->getPapeisPorUsuarioId($usuario['id']);
        }

        return [
            'data' => $usuarios,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM usuario WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' =>$id]);
        $usuario =$stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null;
        }

        $usuario['papeis'] = $this->getPapeisPorUsuarioId($id);
        return $usuario;
    }

    public function getPapeisPorUsuarioId(int $usuarioId): array
    {
        $sql = "SELECT p.* FROM papel p 
                JOIN usuario_papeis up ON p.id = up.papel_id 
                WHERE up.usuario_id = :usuario_id AND up.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' =>$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPapeisAtivos(): array
    {
        $sql = "SELECT * FROM papel WHERE ativo = 1";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $dados, array$papeisIds = []): bool
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO usuario (nome, email, senha, telefone, data_nascimento, criado_at, atualizado_at) 
                    VALUES (:nome, :email, :senha, :telefone, :data_nascimento, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $stmt =$this->pdo->prepare($sql);$stmt->execute([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                'telefone' => $dados['telefone'] ?? null,
                'data_nascimento' => $dados['data_nascimento'] ?? null
            ]);

            $usuarioId = (int)$this->pdo->lastInsertId();

            if (!empty($papeisIds)) {$sqlPivot = "INSERT INTO usuario_papeis (usuario_id, papel_id, data_hora_inicio, ativo) VALUES (:usuario_id, :papel_id, NOW(), 1)";
                $stmtPivot = $this->pdo->prepare($sqlPivot);
                foreach ($papeisIds as $papelId) {$stmtPivot->execute([
                        'usuario_id' => $usuarioId,
                        'papel_id' => $papelId
                    ]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {$this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $dados, array$papeisIds = []): bool
    {
        try {
            $this->pdo->beginTransaction();

            $fields = [
                "nome = :nome",
                "email = :email",
                "telefone = :telefone",
                "data_nascimento = :data_nascimento",
                "atualizado_at = CURRENT_TIMESTAMP"
            ];
            $params = [
                'id' => $id,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'data_nascimento' => $dados['data_nascimento'] ?? null
            ];

            if (!empty($dados['senha'])) {$fields[] = "senha = :senha";
                $params['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            }

            $sql = "UPDATE usuario SET " . implode(", ", $fields) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // Sincronização de papéis na tabela pivot
            $papeisAtuais = $this->getPapeisPorUsuarioId($id);
            $papeisAtuaisIds = array_column($papeisAtuais, 'id');

            // Desativa papéis que foram desmarcados
            $removerIds = array_diff($papeisAtuaisIds,$papeisIds);
            if (!empty($removerIds)) {$sqlDesativar = "UPDATE usuario_papeis SET ativo = 0, data_hora_fim = NOW() WHERE usuario_id = :usuario_id AND papel_id = :papel_id";
                $stmtDesativar = $this->pdo->prepare($sqlDesativar);
                foreach ($removerIds as $papelId) {$stmtDesativar->execute(['usuario_id' => $id, 'papel_id' =>$papelId]);
                }
            }

            // Adiciona ou reativa novos papéis
            foreach ($papeisIds as$papelId) {
                if (!in_array($papelId, $papeisAtuaisIds)) {$sqlCheck = "SELECT id FROM usuario_papeis WHERE usuario_id = :usuario_id AND papel_id = :papel_id LIMIT 1";
                    $stmtCheck =$this->pdo->prepare($sqlCheck);$stmtCheck->execute(['usuario_id' => $id, 'papel_id' =>$papelId]);
                    if ($stmtCheck->fetch()) {$sqlReativar = "UPDATE usuario_papeis SET ativo = 1, data_hora_inicio = NOW(), data_hora_fim = NULL WHERE usuario_id = :usuario_id AND papel_id = :papel_id";
                        $stmtReativar =$this->pdo->prepare($sqlReativar);$stmtReativar->execute(['usuario_id' => $id, 'papel_id' =>$papelId]);
                    } else {
                        $sqlAttach = "INSERT INTO usuario_papeis (usuario_id, papel_id, data_hora_inicio, ativo) VALUES (:usuario_id, :papel_id, NOW(), 1)";
                        $stmtAttach =$this->pdo->prepare($sqlAttach);$stmtAttach->execute(['usuario_id' => $id, 'papel_id' =>$papelId]);
                    }
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {$this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM usuario WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' =>$id]);
    }

    public function autocompleteMotoristas(string $term, ?int $currentUsuarioId = null): array
    {
        $sql = "SELECT u.id, u.nome as name, u.email FROM usuario u
                JOIN usuario_papeis up ON u.id = up.usuario_id
                JOIN papel p ON up.papel_id = p.id
                WHERE p.descricao = 'Motorista'
                  AND p.ativo = 1
                  AND up.ativo = 1
                  AND (up.data_hora_fim IS NULL OR up.data_hora_fim > NOW())
                  AND (NOT EXISTS (SELECT 1 FROM motorista m WHERE m.usuario_id = u.id) " . ($currentUsuarioId ? "OR u.id = :current_id" : "") . ")
                  AND (u.nome LIKE :term OR u.email LIKE :term)
                LIMIT 8";

        $stmt = $this->pdo->prepare($sql);
        if ($currentUsuarioId) {
            $stmt->bindValue(':current_id',$currentUsuarioId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':term', "%{$term}%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}