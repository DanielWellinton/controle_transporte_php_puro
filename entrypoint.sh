#!/bin/bash

# Se a pasta vendor nÃ£o existir, instala as dependÃªncias automaticamente
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "A instalar dependÃªncias do Composer automaticamente..."
    composer install --no-interaction --optimize-autoloader
fi

# Continua a execuÃ§Ã£o normal do container (php-fpm)
exec "$@"