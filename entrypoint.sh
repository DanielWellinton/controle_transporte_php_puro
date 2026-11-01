#!/bin/bash

# Se a pasta vendor não existir, instala as dependências automaticamente
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "A instalar dependências do Composer automaticamente..."
    composer install --no-interaction --optimize-autoloader
fi

# Continua a execução normal do container (php-fpm)
exec "$@"