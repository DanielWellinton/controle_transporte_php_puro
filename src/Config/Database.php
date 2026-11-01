<?php

class Database
{
    private static ?PDO $conexao = null;

    public static function conectar(): ?PDO
    {
        if (self::$conexao === null) {
            try {
                $driver   = $_ENV['DB_DRIVER'] ?: 'mysql';
                $host     = $_ENV['DB_HOST'] ?: 'localhost';
                $port     = $_ENV['DB_PORT'] ?: '3306';
                $dbname   = $_ENV['DB_NAME'];
                $user     = $_ENV['DB_USER'];
                $password = $_ENV['DB_PASSWORD'];

                $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                
                self::$conexao = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
                
                throw new Exception("Não foi possível conectar ao banco de dados. Tente novamente mais tarde.");
            }
        }

        return self::$conexao;
    }
}