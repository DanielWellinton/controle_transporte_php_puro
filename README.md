# 🚀 Sistema de Gestão de Viagens e Embarque via QR Code

> Sistema web desenvolvido em PHP puro com arquitetura modular, suporte a autenticação JWT sincronizada com sessões, controlo de acessos baseado em papéis (RBAC) e leitor de QR Code para validação de embarques.

---

## 🛠️ Tecnologias Utilizadas

- **PHP 8.2** (FPM & PDO)
- **Nginx**
- **MySQL 8.0**
- **Docker & Docker Compose**
- **Tailwind CSS** (Interface moderna e responsiva)

---

## 📋 Pré-requisitos

Antes de começar, certifique-se de que tem instalado na sua máquina:
- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com/)

---

## ⚙️ Guia de Instalação e Configuração

Siga os passos detalhados abaixo para configurar e executar o ambiente do projeto localmente.

### 1. Clonar o Repositório

```bash
git clone https://github.com/DanielWellinton/controle_transporte_php_puro.git
cd controle_transporte_php_puro
```

### 2. Configurar o Ficheiro `.env`

Crie o ficheiro `.env` na raiz do projeto com as seguintes variáveis de ambiente:

```env
DB_DRIVER=mysql
DB_HOST=db
DB_PORT=3306
DB_NAME=nome_do_banco
DB_USER=seu_usuario
DB_PASSWORD=sua_senha
SECRET_KEY=sua_chave_secreta_super_segura
```

### 3. Configurar o Nginx

Copie o template de configuração do Nginx para criar o ficheiro que será utilizado pelo servidor:

```bash
cp docker/nginx/default-template.conf docker/nginx/default.conf
```

### 4. Subir os Containers Docker

Inicie os serviços da aplicação em segundo plano:

```bash
docker-compose up -d
```

### 5. Criação e Execução das Migrações

Com os containers em execução, configure a base de dados:

```bash
# Criação da tabela de migração
docker-compose exec app php bin/console migrate:init

# Roda as migrações para construir a estrutura do banco de dados
docker-compose exec app php bin/console migrate

# Verificar se todas as migrações foram executadas com sucesso
docker-compose exec app php bin/console migrate:list
```

---

## 📱 Testar o Scanner e Acesso Externo (SSL / HTTPS)

Para testar a leitura de QR Code num dispositivo externo (como um smartphone conectado na mesma rede Wi-Fi), execute o script de atualização de certificados SSL:

```bash
sh update-ssl.sh
```

Em seguida, aceda ao sistema através do endereço IP da sua máquina na rede local:
```text
https://192.168.X.X
```
