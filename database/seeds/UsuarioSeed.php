<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Services\UsuarioService;

class UsuarioSeed
{

    public function run(): void
    {
        $pdo = Database::conectar();
        $usuarioRepository = new UsuarioRepository($pdo);
        $usuarioService = new UsuarioService($usuarioRepository);

        $usuarios = [

            [
                'dados' => [
                    'nome' => 'admin',
                    'email' => 'admin@transporte.com',
                    'senha' => 'password',
                    'senha_confirmation' => 'password',
                    'telefone' => null,
                    'data_nascimento' => '1990-01-01',
                ],
                'papeis' => [1,2,3]
            ],

            [
                'dados' => [
                    'nome' => 'Joaquim',
                    'email' => 'joaquim@transporte.com',
                    'senha' => 'joaquim123',
                    'senha_confirmation' => 'joaquim123',
                    'telefone' => null,
                    'data_nascimento' => '1970-01-15',
                ],
                'papeis' => [2,3]
            ],

            [
                'dados' => [
                    'nome' => 'Maria',
                    'email' => 'maria@transporte.com',
                    'senha' => 'maria123',
                    'senha_confirmation' => 'maria123',
                    'telefone' => null,
                    'data_nascimento' => '1980-10-20',
                ],
                'papeis' => [2,3]
            ],

            [
                'dados' => [
                    'nome' => 'Maria',
                    'email' => 'maria@gmail.com',
                    'senha' => 'maria123',
                    'senha_confirmation' => 'maria123',
                    'telefone' => null,
                    'data_nascimento' => '2000-07-20',
                ],
                'papeis' => [3]
            ],

            [
                'dados' => [
                    'nome' => 'Luiz',
                    'email' => 'luiz@gmail.com',
                    'senha' => 'luiz123',
                    'senha_confirmation' => 'luiz123',
                    'telefone' => null,
                    'data_nascimento' => '2005-11-19',
                ],
                'papeis' => [3]
            ],

            [
                'dados' => [
                    'nome' => 'Henrique',
                    'email' => 'henrique@gmail.com',
                    'senha' => 'henrique123',
                    'senha_confirmation' => 'henrique123',
                    'telefone' => null,
                    'data_nascimento' => '2006-05-07',
                ],
                'papeis' => [3]
            ],

             [
                'dados' => [
                    'nome' => 'Daniel',
                    'email' => 'daniel@gmail.com',
                    'senha' => 'daniel123',
                    'senha_confirmation' => 'daniel123',
                    'telefone' => null,
                    'data_nascimento' => '2004-01-04',
                ],
                'papeis' => [3]
            ],
            
        ];

        foreach ($usuarios as $item) {
            $email = $item['dados']['email'];

            if ($usuarioRepository->findByEmail($email)) {
                echo "[IGNORADO] E-mail já cadastrado: {$email}\n";
                continue;
            }

            $usuarioService->cadastrar($item['dados'], $item['papeis']);
            echo "[OK] Usuário cadastrado: {$email}\n";
        }
    }
    
}

(new UsuarioSeed())->run();