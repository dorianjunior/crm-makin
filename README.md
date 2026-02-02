# 🎯 CRM Makin

Sistema de CRM (Customer Relationship Management) completo desenvolvido com Laravel 12, focado em gestão de leads, vendas, pipelines e comunicação com clientes.

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias](#tecnologias)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Seeders](#seeders)
- [API Endpoints](#api-endpoints)
- [Autenticação](#autenticação)
- [Estrutura do Banco](#estrutura-do-banco)
- [Documentação](#documentação)

## 🎯 Sobre o Projeto

CRM Makin é uma solução completa de gestão de relacionamento com clientes que oferece:

- ✅ Gestão multi-empresa (multi-tenancy)
- ✅ Sistema de permissões e roles granulares
- ✅ Gestão completa de leads e pipeline de vendas
- ✅ Comunicação integrada (Email, WhatsApp)
- ✅ Gestão de produtos e propostas comerciais
- ✅ Sistema de tarefas e atividades
- ✅ Upload e gerenciamento de arquivos
- ✅ Logs de sistema para auditoria
- ✅ API RESTful completa

## ⚡ Funcionalidades

### 🏢 Multi-tenancy
- Suporte para múltiplas empresas
- Dados isolados por empresa
- Planos diferenciados (Free, Basic, Premium, Enterprise)

### 👥 Gestão de Usuários
- Sistema de roles e permissões
- 5 roles pré-configurados (Admin, Manager, Sales, Support, Viewer)
- 65+ permissões granulares
- Controle de usuários ativos/inativos

### 📊 Gestão de Leads
- Cadastro completo de leads
- Múltiplas fontes de leads
- Atribuição automática de vendedores
- Status personalizáveis
- Histórico de atividades

### 🔄 Pipeline de Vendas
- Pipelines customizáveis
- Estágios configuráveis
- Drag & drop de leads entre estágios
- Múltiplos pipelines por empresa

### 💰 Produtos & Propostas
- Catálogo de produtos
- Geração de propostas comerciais
- Cálculo automático de valores
- Status de propostas (Draft, Sent, Accepted, Rejected)

### 💬 Comunicação
- Integração com Email
- Integração com WhatsApp
- Templates de mensagens reutilizáveis
- Histórico completo de comunicações

### 📁 Gestão de Arquivos
- Upload de documentos
- Vinculação com leads
- Download seguro
- Limite de 10MB por arquivo

### 📝 Tarefas & Atividades
- Criação de tarefas vinculadas a leads
- Registro de atividades (calls, emails, meetings, notes)
- Datas de vencimento
- Status de tarefas

## 🛠️ Tecnologias

- **Backend:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL 8.4
- **Authentication:** Laravel Sanctum
- **Containerization:** Docker & Docker Compose
- **Package Manager:** Composer

## 📦 Requisitos

- PHP >= 8.2
- Composer
- Docker & Docker Compose
- MySQL 8.4 (via Docker)
- Node.js & NPM (opcional, para frontend)

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone <repository-url>
cd crm-api
```

### 2. Instale as dependências

```bash
composer install
```

### 3. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure o banco de dados

Edite o arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 5. Inicie o Docker (MySQL)

```bash
cd ../crm-data
docker compose up -d
```

### 6. Execute as migrations

```bash
cd ../crm-api
php artisan migrate
```

### 7. Execute os seeders

```bash
php artisan db:seed
```

### 8. Inicie o servidor

```bash
php artisan serve
```

A API estará disponível em: `http://localhost:8000`

## ⚙️ Configuração

### Docker MySQL

O projeto usa Docker para o MySQL. Configure as variáveis no `.env` da pasta `crm-data`:

```env
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=crm
MYSQL_USER=crm_user
MYSQL_PASSWORD=crm_password
MYSQL_PORT=3306
```

### Sanctum

O Laravel Sanctum já está configurado. Para SPAs, configure os domínios permitidos em `config/sanctum.php`.

## 🌱 Seeders

O projeto inclui seeders completos com dados de exemplo:

```bash
php artisan db:seed
```

Isso criará:
- ✅ 65 Permissions
- ✅ 5 Roles (Admin, Manager, Sales, Support, Viewer)
- ✅ 3 Companies
- ✅ 5 Users (senha: `password`)
- ✅ 6 Pipelines com 30 stages
- ✅ 36 Lead Sources
- ✅ 8 Products
- ✅ 8 Message Templates
- ✅ 8 Leads de exemplo

### Credenciais de Teste

```
Email: admin@demo.com
Senha: password

Email: manager@demo.com
Senha: password

Email: john@demo.com
Senha: password
```

## 🔌 API Endpoints

### Autenticação
```
POST   /api/auth/register    - Registrar usuário
POST   /api/auth/login       - Login
POST   /api/auth/logout      - Logout
GET    /api/auth/user        - Usuário autenticado
```

### Recursos (CRUD Completo)
```
/api/companies              - Empresas
/api/roles                  - Roles
/api/permissions            - Permissões
/api/users                  - Usuários
/api/lead-sources           - Fontes de leads
/api/leads                  - Leads
/api/activities             - Atividades
/api/tasks                  - Tarefas
/api/pipelines              - Pipelines
/api/pipeline-stages        - Estágios de pipeline
/api/products               - Produtos
/api/proposals              - Propostas
/api/emails                 - Emails
/api/whatsapp-messages      - Mensagens WhatsApp
/api/message-templates      - Templates de mensagens
/api/files                  - Arquivos
/api/system-logs            - Logs do sistema
```

Veja a [documentação completa da API](docs/API_ENDPOINTS.md).

## 🔐 Autenticação

Todas as rotas protegidas requerem um token Bearer:

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@demo.com","password":"password"}'

# Usar o token
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

Veja a [documentação de autenticação](docs/AUTHENTICATION.md).

## 🗄️ Estrutura do Banco

### Principais Tabelas

- **companies** - Empresas (multi-tenancy)
- **users** - Usuários do sistema
- **roles** - Funções/papéis
- **permissions** - Permissões
- **role_permissions** - Pivot roles-permissions
- **leads** - Leads/Prospects
- **lead_sources** - Fontes de leads
- **activities** - Atividades dos leads
- **tasks** - Tarefas
- **pipelines** - Funis de venda
- **pipeline_stages** - Estágios dos funis
- **lead_pipeline** - Pivot leads-stages
- **products** - Produtos/Serviços
- **proposals** - Propostas comerciais
- **proposal_items** - Itens das propostas
- **emails** - Emails enviados
- **whatsapp_messages** - Mensagens WhatsApp
- **message_templates** - Templates de mensagens
- **files** - Arquivos
- **system_logs** - Logs de auditoria

## 📚 Documentação

- [API Endpoints](docs/API_ENDPOINTS.md) - Documentação completa da API
- [Autenticação](docs/AUTHENTICATION.md) - Como usar autenticação Sanctum
- [Seeders](docs/SEEDERS.md) - Detalhes sobre os dados de exemplo

## 🧪 Testes

```bash
# Executar todos os testes
php artisan test

# Com coverage
php artisan test --coverage
```

## 🔄 Comandos Úteis

```bash
# Resetar banco e popular novamente
php artisan migrate:fresh --seed

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rotas
php artisan route:list

# Criar migration
php artisan make:migration create_exemplo_table

# Criar model
php artisan make:model Exemplo -mcr
```

## 📝 Licença

Este projeto está sob a licença MIT.

## 👥 Autor

Desenvolvido por **Dorian** - CRM Makin

---

⭐ Se este projeto foi útil, considere dar uma estrela!


In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
