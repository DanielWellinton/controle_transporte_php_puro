# 🚌 BusRoute — Sistema Web de Gestão de Viagens e Embarque via QR Code

> Sistema web interdisciplinar desenvolvido em **PHP 8 orientado a objetos**, estruturado em arquitetura modular em camadas (MVC, Front Controller, Services e Repositories), com autenticação via **JSON Web Tokens (JWT)**, controle de acesso baseado em papéis (**RBAC**), banco de dados relacional normalizado via **PDO** e leitor óptico de **QR Code** para validação de embarques.

---

## 👥 Equipe de Desenvolvimento

Projeto interdisciplinar desenvolvido no curso de **Ciência da Computação** da **Universidade do Oeste de Santa Catarina (UNOESC — Campus de Videira)**, integrando os componentes curriculares de **Engenharia de Software**, **Banco de Dados II** e **Programação II**:

- **Daniel Wellinton Bortolini Bortolossi**
- **Henrique Gabriel da Silva Pereira**
- **Luiz Henrique dos Santos**

---

## 🎯 Objetivo do Projeto

Informatizar e rastrear o controle operacional do transporte universitário por meio de uma plataforma web unificada que integra três perfis de usuários:

- **Gestor / Administrador:** gerenciamento de usuários, papéis de acesso, motoristas, frota de veículos, pontos de parada, ordenação de itinerários e agendamento de viagens com geração automática de código QR.
- **Passageiro:** consulta de viagens disponíveis, seleção ou alteração prévia dos pontos de embarque e desembarque, validação digital de embarque via leitura óptica de QR Code no veículo e histórico de deslocamentos.
- **Motorista:** painel de viagens atribuídas, conferência de passageiros confirmados e presentes por ponto de parada e emissão de manifesto de embarque formatado para impressão física (contingência offline).

---

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 8.2 (FPM, Programação Orientada a Objetos, PSR-4 via Composer, PDO)
- **Banco de Dados:** MySQL 8.0 (Modelagem relacional normalizada 3FN e CLI próprio de migrações)
- **Servidor Web & Infraestrutura:** Nginx, Docker e Docker Compose com suporte a certificados SSL/TLS
- **Segurança:** Autenticação stateless com JWT (`Firebase\JWT` / assinatura HMAC), hash de senhas com salt e autorização RBAC via Middlewares
- **Frontend:** HTML5, CSS3 (Tailwind CSS), JavaScript (leitura óptica de câmera via biblioteca de QR Code)

---

## 📋 Pré-requisitos

Antes de iniciar, certifique-se de ter instalado na sua máquina:

- [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com/) (no Windows, inclui o **Git Bash** e o `openssl` necessários para geração de certificados SSL)

---

## ⚙️ Guia de Instalação e Execução Local

### 1. Clonar o Repositório

```bash
git clone [https://github.com/DanielWellinton/controle_transporte_php_puro.git](https://github.com/DanielWellinton/controle_transporte_php_puro.git)
cd controle_transporte_php_puro
```

### 2. Configurar as Variáveis de Ambiente (`.env`)

Crie um arquivo `.env` na raiz do projeto (baseado no `.env.example`, sem versionar credenciais reais no Git) com a seguinte estrutura:

```env
DB_DRIVER=mysql
DB_HOST=db
DB_PORT=3306
DB_PORTLOCAL=3307
DB_NAME=busroute
DB_USER=busroute_user
DB_PASSWORD=busroute_pass
DB_TIMEZONE=-03:00
SECRET_KEY=sua_chave_secreta_super_segura
```

### 3. Preparar Arquivos e Subir os Contêineres

Siga as instruções correspondentes ao seu sistema operacional:

#### 🪟 Ambiente Windows (PowerShell)

No Windows, o Git pode converter automaticamente as quebras de linha dos scripts `.sh` para `CRLF`, impedindo o contêiner PHP de iniciar. Execute os comandos abaixo no **PowerShell**:

```powershell
# 1. Converte os scripts .sh para o padrão Unix (LF)
Get-ChildItem -Recurse -Filter *.sh | ForEach-Object {
    $c = Get-Content $_.FullName -Raw
    [System.IO.File]::WriteAllText($_.FullName, $c.Replace("`r`n", "`n"))
}

# 2. Copia o template de configuração do Nginx
Copy-Item docker/nginx/default-template.conf docker/nginx/default.conf

# 3. Constrói e inicia os contêineres em segundo plano
docker-compose up -d --build
```

#### 🐧 / 🍎 Ambiente Linux ou macOS (Terminal / Bash)

```bash
# 1. Copia o template de configuração do Nginx
cp docker/nginx/default-template.conf docker/nginx/default.conf

# 2. Constrói e inicia os contêineres em segundo plano
docker-compose up -d --build
```

---

### 4. Executar as Migrações do Banco de Dados (CLI)

Verifique se os três contêineres (`app`, `db` e `web`) estão com status `Up` (`docker-compose ps`), aguarde alguns segundos para a inicialização completa do MySQL e execute:

```bash
# 1. Criar a tabela de controle de versões do esquema
docker-compose exec app php bin/console migrate:init

# 2. Executar todas as migrações sequenciais (001 a 012)
docker-compose exec app php bin/console migrate

# 3. Conferir o status das migrações aplicadas
docker-compose exec app php bin/console migrate:list
```

### 5. Executar as Seeds

Após subir as migrações, popule o banco de dados executando os scripts abaixo na ordem indicada para respeitar os relacionamentos entre as tabelas:

```bash
#Para poder rodar todas as seeds de uma vez só:
docker-compose exec app php database/seeds/DataBaseSeeder.php

#Para poder rodar seed por seed de cada vez:
docker-compose exec app php database/seeds/UsuarioSeed.php
docker-compose exec app php database/seeds/MotoristaSeed.php
docker-compose exec app php database/seeds/VeiculoSeed.php
docker-compose exec app php database/seeds/PontoDeParadaSeed.php
docker-compose exec app php database/seeds/RotaSeed.php
docker-compose exec app php database/seeds/ViagemSeed.php
```

### 6. Acessar a Aplicação no Navegador

Com os contêineres ativos e o banco migrado, acesse a interface web localmente via HTTP:

```text
http://localhost:8080
```

*(Caso precise atualizar o autoloader PSR-4 do Composer após adicionar novas classes: `docker-compose exec app composer dump-autoload -o`)*

---

## 📱 Acesso via Smartphone e Validação do Leitor de QR Code (HTTPS / SSL)

Os navegadores móveis exigem conexão segura (**HTTPS**) para liberar o acesso à câmera do dispositivo em endereços de rede local. Siga os passos para o seu sistema operacional com o smartphone conectado à mesma rede Wi-Fi:

#### 🪟 Ambiente Windows (PowerShell)

```powershell
# 1. Descubra o endereço IPv4 local da sua máquina
ipconfig | Select-String "IPv4"

# 2. Gere o certificado SSL substituindo 192.168.X.X pelo IP encontrado
& "C:\Program Files\Git\bin\sh.exe" update-ssl-windows.sh 192.168.X.X

# 3. Reinicie o contêiner do Nginx para carregar o certificado
docker-compose restart web
```

#### 🐧 / 🍎 Ambiente Linux ou macOS (Terminal / Bash)

```bash
# 1. Descubra o endereço IPv4 local da sua máquina
hostname -I

# 2. Gere o certificado SSL substituindo 192.168.X.X pelo IP encontrado
sh update-ssl.sh 192.168.X.X

# 3. Reinicie o contêiner do Nginx para carregar o certificado
docker-compose restart web
```

#### 📲 Abrindo no Smartphone

Acesse pelo navegador do celular (na mesma rede Wi-Fi):

```text
[https://192.168.](https://192.168.)X.X
```

*(Aceite o aviso de certificado autoassinado local em **Avançado → Continuar** e conceda permissão de uso da câmera ao abrir o scanner).*

---

## 📂 Estrutura de Diretórios

```text
controle_transporte_php_puro/
├── bin/
│   └── console                 # Ferramenta CLI para gerenciamento de migrações
├── database/
│   └── migrations/             # Scripts evolutivos do banco relacional (001 a 012)
├── docker/
│   └── nginx/                  # Configurações de virtual host e certificados SSL
├── public/
│   └── index.php               # Front Controller (ponto único de entrada HTTP)
├── src/
│   ├── Config/                 # Carregamento de ambiente (.env) e conexão PDO
│   ├── Console/                # Comandos CLI (MigrateInit, MigrateMake, MigrateList, Migrar)
│   ├── Controllers/            # Controladores HTTP (10 controladores)
│   ├── Helpers/                # Funções auxiliares de visão e formatação
│   ├── Middleware/             # Interceptadores de autenticação JWT e autorização RBAC
│   ├── Models/                 # Entidades de domínio OO estendendo Model (11 classes)
│   ├── Repositories/           # Persistência relacional via PDO parametrizado (9 repositórios)
│   ├── Services/               # Regras de negócio e orquestração (10 serviços)
│   └── Views/                  # Templates de apresentação (.phtml) e layouts modulares
├── docker-compose.yml          # Orquestração dos serviços PHP-FPM, MySQL e Nginx
├── Dockerfile                  # Definição da imagem PHP 8.2 com extensões PDO e Composer
├── entrypoint.sh               # Script de inicialização do contêiner PHP
├── update-ssl.sh               # Gerador multiplataforma de certificados SSL locais
└── README.md                   # Documentação de instalação e execução
```