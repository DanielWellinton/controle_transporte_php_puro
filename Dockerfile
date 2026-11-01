FROM php:8.2-fpm

# Instalar dependências do sistema e extensões necessárias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# Instalar o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar o composer.json primeiro para aproveitar a cache das camadas
COPY composer.json ./
COPY src/ ./src/

# Instalar as dependências (gera a pasta vendor dentro do container)
RUN composer install --no-dev --optimize-autoloader

# Copiar o restante código do projeto
COPY . .

# Ajustar permissões da aplicação
RUN chown -R www-data:www-data /var/www/html