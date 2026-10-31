<?php

class MigracaoRepository
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarPorId(int $id): ?Migracao
    {
        $stmt = $this->pdo->prepare('SELECT * FROM migracao WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($linha === false) {
            return null;
        }

        $migracao = new Migracao();
        $migracao->setId($linha['id']);
        $migracao->setDescricao($linha['descricao']);
        $migracao->setAtivo((bool) $linha['ativo']);

        return $migracao;
    }

    public function create(string $descricao, bool $ativo = false): ?Migracao
    {
        try {
            $stmtCheck = $this->pdo->prepare('SELECT id FROM migracao WHERE descricao = :descricao LIMIT 1');
            $stmtCheck->execute(['descricao' => $descricao]);
            
            $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if ($existente) {
                return $this->buscarPorId((int) $existente['id']);
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO migracao
                (descricao, ativo)
                VALUES (:descricao, :ativo)'
            );

            $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
            $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);

            $stmt->execute();
            $novoId = (int) $this->pdo->lastInsertId();

            return $this->buscarPorId($novoId);
            
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao cadastrar migração: " . $e->getMessage());
        }
    }

    public function update(Migracao $migracao): ?Migracao
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE migracao 
                SET descricao = :descricao, ativo = :ativo 
                WHERE id = :id'
            );

            $descricao = $migracao->getDescricao();
            $ativo = $migracao->getAtivo();
            $id = $migracao->getId();

            $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
            $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            return $this->buscarPorId($id);
            
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao atualizar migração: " . $e->getMessage());
        }
    }

    public function executeScript(string $script)
    {
        try {
            $stmt = $this->pdo->prepare(
                $script
            );
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao executar script: " . $e->getMessage());
        }
    }
}
