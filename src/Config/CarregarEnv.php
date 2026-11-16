<?php

class CarregarEnv {
    public function __construct()
    {
        self::carregarEnv();
    }

    private static function carregarEnv(): void
    {
        $caminhoEnv = __DIR__ . '/../../.env';
        if (file_exists($caminhoEnv)) {
            $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($linhas as $linha) {
                if (str_starts_with(trim($linha), '#')) continue;
                list($chave, $valor) = explode('=', $linha, 2) + ['', ''];
                $chave = trim($chave);
                $valor = trim($valor);
                if (!empty($chave) && !array_key_exists($chave, $_ENV)) {
                    $_ENV[$chave] = $valor;
                    $_SERVER[$chave] = $valor;
                    putenv("$chave=$valor");
                }
            }
        }
    }
}