FROM php:8.2-fpm

# Instalar dependências de sistema, extensões necessárias e ferramentas (git, unzip, libzip, curl)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    curl \
    && docker-php-ext-install pdo_mysql zip

# Instalar o mkcert oficial de forma correta
RUN curl -L -o /usr/local/bin/mkcert "https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64" \
    && chmod +x /usr/local/bin/mkcert

# Instalar o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar o script de entrypoint e dar permissão de execução diretamente
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Definir o entrypoint padrão
ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]