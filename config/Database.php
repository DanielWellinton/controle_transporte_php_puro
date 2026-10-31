<?php

class Database
{
    private static ?PDO $conexao = null;

    public static function conectar(): ?PDO
    {
        if (self::$conexao === null) {
            try {
                $driver   = getenv('DB_DRIVER') ?: 'mysql';
                $host     = getenv('DB_HOST') ?: 'localhost';
                $port     = getenv('DB_PORT') ?: '3306';
                $dbname   = getenv('DB_NAME');
                $user     = getenv('DB_USER');
                $password = getenv('DB_PASSWORD');

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