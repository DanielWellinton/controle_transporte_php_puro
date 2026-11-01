<?php

if (!function_exists('view')) {
    function view(string $nome, array $dados = [], string $layout = 'layouts/app'): void
    {
        // Extrai as variáveis para ficarem disponíveis na view
        extract($dados);

        // Caminho da view específica (ex: src/Views/motorista/index.phtml)
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $nome) . '.phtml';

        if (!file_exists($viewPath)) {
            throw new Exception("A view [{$nome}] não foi encontrada no caminho: {$viewPath}");
        }

        // Inicia o buffer para capturar o HTML da view filha
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Caminho do layout base (ex: src/Views/layouts/app.phtml)
        $layoutPath = __DIR__ . '/../Views/' . str_replace('.', '/', $layout) . '.phtml';

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            // Se não houver layout, imprime apenas o conteúdo
            echo $content;
        }
    }
}