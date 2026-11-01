<?php

namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private static string $encryptAlgorithm = 'HS256';

    private static function getSecretKey(): string
    {
        $key = $_ENV['SECRET_KEY'] ?? getenv('SECRET_KEY');
        
        if (!$key) {
            throw new \Exception("A chave secreta (SECRET_KEY) não está definida no ambiente.");
        }
        
        return $key;
    }

    public static function gerarToken(array $dadosUsuario): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + (86400 * 7), // Expira em 7 dias
            'data' => $dadosUsuario
        ];

        return JWT::encode($payload, self::getSecretKey(), self::$encryptAlgorithm);
    }

    public static function validarToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), self::$encryptAlgorithm));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return null; // Token inválido ou expirado
        }
    }
}