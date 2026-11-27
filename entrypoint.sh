#!/bin/bash

# Se a pasta vendor nÃƒÂ£o existir, instala as dependÃƒÂªncias automaticamente
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "A instalar dependÃƒÂªncias do Composer automaticamente..."
    composer install --no-interaction --optimize-autoloader
fi

# Continua a execuÃƒÂ§ÃƒÂ£o normal do container (php-fpm)
exec "$@"