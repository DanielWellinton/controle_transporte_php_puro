copiar o template das configurações do nginx para o que irá usá-lo
cp docker/nginx/default-template.conf docker/nginx/default.conf

subir os containers
docker-compose up -d

criacao da tabela migracao
docker-compose exec app php bin/console migrate:init

roda as migracoes para construir o banco de dados
docker-compose exec app php bin/console migrate

para testar o scanner
