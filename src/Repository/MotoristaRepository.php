<?php

class MotoristaRepository
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Motorista[]|null
     */
    public function getAll(): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM motorista');
        
        $stmt->execute(); 
        
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($linhas)) {
            return null;
        }

        $Motoristas = [];
        foreach ($linhas as $linha) {
            $Motorista = new Motorista(
                $linha['titulo'],
                $linha['isbn'],
                (int)$linha['ano_publicacao'],
                (float)$linha['preco'],
                (int)$linha['id_autor'],
                (int)$linha['quantidade']
            );
            $Motorista->setId((int)$linha['id']);
            
            $Motoristas[] = $Motorista;
        }

        return $Motoristas;
    }

    public function buscarPorId(int $id): ?Motorista
    {
        $stmt = $this->pdo->prepare('SELECT id, titulo, isbn, ano_publicacao, preco, quantidade, id_autor FROM Motorista WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($linha === false) {
            return null;
        }

        $Motorista = new Motorista(
            $linha['titulo'],
            $linha['isbn'],
            (int)$linha['ano_publicacao'],
            (float)$linha['preco'],
            (int)$linha['id_autor'],
            (int)$linha['quantidade']
        );
        $Motorista->setId((int)$linha['id']);

        return $Motorista;
    }

    public function create(string $titulo, string $isbn, int $anoPublicacao, float $preco, int $idAutor, int $quantidade = 0)
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO Motorista
                (titulo, isbn, ano_publicacao, preco, quantidade, id_autor)
                VALUES (:titulo, :isbn, :ano_publicacao, :preco, :quantidade, :id_autor)'
            );

            $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
            $stmt->bindParam(':ano_publicacao', $anoPublicacao, PDO::PARAM_INT);
            $stmt->bindParam(':preco', $preco, PDO::PARAM_STR);
            $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
            $stmt->bindParam(':id_autor', $idAutor, PDO::PARAM_INT);

            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new \Exception("Erro: O ISBN '{$isbn}' já está cadastrado ou o ID do autor é inválido.");
            }
            throw new \Exception("Erro ao cadastrar Motorista: " . $e->getMessage());
        }
    }
}
