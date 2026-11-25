#!/bin/bash
set -e

# 1. Detecta IP local (aceita argumento ex: ./update-ssl.sh 192.168.1.10 ou detecta automaticamente)
if [ -n "$1" ]; then
    LOCAL_IP="$1"
elif command -v hostname >/dev/null 2>&1 && hostname -I >/dev/null 2>&1; then
    LOCAL_IP=$(hostname -I | awk '{print $1}')
elif command -v ipconfig >/dev/null 2>&1; then
    LOCAL_IP=$(ipconfig | grep -E "IPv4|Endere.*IPv4" | grep -v "127.0.0.1" | head -n 1 | awk -F: '{print $2}' | tr -d ' \r')
elif command -v ip >/dev/null 2>&1; then
    LOCAL_IP=$(ip route get 1 2>/dev/null | awk '{print $7; exit}')
fi

if [ -z "$LOCAL_IP" ]; then
    LOCAL_IP="127.0.0.1"
fi

echo "IP local detectado: $LOCAL_IP"

CERTS_DIR="./docker/nginx/certs"
NGINX_CONF="./docker/nginx/default.conf"
CERT_FILE="cert.pem"
KEY_FILE="key.pem"

mkdir -p "$(dirname "$NGINX_CONF")"
mkdir -p "$CERTS_DIR"

echo "Limpando certificados antigos..."
rm -f "$CERTS_DIR"/*.pem

echo "Gerando certificado SSL autoassinado para localhost e $LOCAL_IP..."
MSYS_NO_PATHCONV=1 openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$CERTS_DIR/$KEY_FILE" \
    -out "$CERTS_DIR/$CERT_FILE" \
    -subj "/C=BR/ST=SC/L=Videira/O=BusRoute/CN=$LOCAL_IP" \
    -addext "subjectAltName=DNS:localhost,IP:127.0.0.1,IP:$LOCAL_IP"

echo "Atualizando configuracao do Nginx ($NGINX_CONF)..."
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

echo Subindo Docker e reiniciando Nginx...
docker-compose restart web

echo "=================================================="
echo "SSL e Nginx atualizados com sucesso!"
echo "Acesse no seu dispositivo: https://$LOCAL_IP"
echo "=================================================="