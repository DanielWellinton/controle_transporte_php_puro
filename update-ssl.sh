#!/bin/bash

# Exit on error
set -e

LOCAL_IP=$(hostname -I | awk '{print $1}')

if [ -z "$LOCAL_IP" ]; then
    echo "❌ Não foi possível obter o IP local. Verifique sua conexão com a rede."
    exit 1
fi

echo "🔍 IP local detectado: $LOCAL_IP"

CERTS_DIR="./docker/nginx/certs"
NGINX_CONF="./docker/nginx/default.conf"

mkdir -p "$(dirname "$NGINX_CONF")"

mkdir -p "$CERTS_DIR"

echo "🧹 Limpando certificados antigos..."
rm -f "$CERTS_DIR"/*.pem

echo "🔑 Gerando novos certificados para o IP: $LOCAL_IP..."
docker-compose exec app bash -c "/usr/local/bin/mkcert -cert-file /var/www/html/docker/nginx/certs/${LOCAL_IP}.pem -key-file /var/www/html/docker/nginx/certs/${LOCAL_IP}-key.pem ${LOCAL_IP} localhost 127.0.0.1"

# Ajusta as permissões diretamente dentro do container usando o nome exato do IP
docker-compose exec app bash -c "chmod 644 /var/www/html/docker/nginx/certs/${LOCAL_IP}.pem /var/www/html/docker/nginx/certs/${LOCAL_IP}-key.pem"

CERT_FILE=$(ls "$CERTS_DIR" | grep -E "^${LOCAL_IP}.*\.pem$" | grep -v "\-key\.pem$")
KEY_FILE=$(ls "$CERTS_DIR" | grep -E "^${LOCAL_IP}.*-key\.pem$")

if [ -z "$CERT_FILE" ] || [ -z "$KEY_FILE" ]; then
    echo "❌ Falha ao localizar os arquivos gerados pelo mkcert."
    exit 1
fi

echo "📄 Certificado gerado: $CERT_FILE"
echo "🔑 Chave privada gerada: $KEY_FILE"

echo "⚙️ Atualizando a configuração do Nginx ($NGINX_CONF)..."
cat <<EOF > "$NGINX_CONF"
server {
    listen 80;
    listen 443 ssl;
    server_name _;

    ssl_certificate /etc/nginx/certs/$CERT_FILE;
    ssl_certificate_key /etc/nginx/certs/$KEY_FILE;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/html/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

echo "🔄 Subindo/Reiniciando o nginx..."
docker-compose restart web

echo "=================================================="
echo "✅ SSL e Nginx atualizados com sucesso!"
echo "🌐 Acesse no seu dispositivo: https://$LOCAL_IP"
echo "=================================================="