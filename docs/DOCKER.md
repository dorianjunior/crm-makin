# 🐳 Docker Infrastructure for CRM Makin

Este guia detalha como configurar e executar a infraestrutura Docker do CRM Makin.

## 📋 Índice

- [Pré-requisitos](#pré-requisitos)
- [Arquitetura](#arquitetura)
- [Configuração Inicial](#configuração-inicial)
- [Ambientes](#ambientes)
- [Comandos Úteis](#comandos-úteis)
- [Troubleshooting](#troubleshooting)
- [Performance](#performance)
- [Segurança](#segurança)

---

## 🔧 Pré-requisitos

### Software Necessário

- **Docker Engine**: 20.10+
- **Docker Compose**: 2.0+
- **Git**: Para clonar o repositório
- **Make** (opcional): Para usar os comandos Makefile

### Recursos do Sistema

**Mínimo (Desenvolvimento):**
- 4 GB RAM
- 2 CPU cores
- 10 GB espaço em disco

**Recomendado (Desenvolvimento):**
- 8 GB RAM
- 4 CPU cores
- 20 GB espaço em disco

**Produção:**
- 16 GB RAM
- 4-8 CPU cores
- 50 GB SSD

---

## 🏗️ Arquitetura

### Containers

```
┌─────────────────────────────────────────────────────────────┐
│                     Docker Compose Stack                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────────┐  ┌────────┐ │
│  │  Nginx   │  │   App    │  │ Queue Worker │  │ Sched  │ │
│  │  :80     │──│ PHP-FPM  │  │  (Laravel)   │  │ (Cron) │ │
│  │          │  │  :9000   │  │              │  │        │ │
│  └──────────┘  └──────────┘  └──────────────┘  └────────┘ │
│       │             │                │               │      │
│       │        ┌────┴────────────────┴───────────────┘      │
│       │        │                                            │
│  ┌────┴────────┴──────────────┐     ┌──────────────────┐  │
│  │       MariaDB 11.2         │     │   Redis 7.2      │  │
│  │       Port: 3306           │     │   Port: 6379     │  │
│  │       Volume: mysql-data   │     │   Cache + Queue  │  │
│  └────────────────────────────┘     └──────────────────┘  │
│                                                             │
│            Dev Only (docker-compose.dev.yml):              │
│  ┌────────────┐  ┌────────────┐  ┌─────────────────────┐  │
│  │  Mailhog   │  │ phpMyAdmin │  │ Redis Commander     │  │
│  │   :8025    │  │   :8080    │  │      :8081          │  │
│  └────────────┘  └────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Multi-Stage Dockerfile

O Dockerfile usa 8 estágios otimizados:

1. **composer-deps**: Instala dependências PHP de produção
2. **composer-deps-dev**: Instala dependências de desenvolvimento
3. **node-deps**: Instala dependências Node.js
4. **assets-build**: Compila assets frontend (Vite)
5. **runtime-base**: Imagem base PHP-FPM Alpine
6. **development**: Target de desenvolvimento (com Xdebug)
7. **production**: Target de produção (otimizado)
8. **queue-worker**: Container especializado para queues

---

## ⚙️ Configuração Inicial

### 1. Clonar o Repositório

```bash
git clone <repository-url>
cd crm-makin/crm-api
```

### 2. Configurar Variáveis de Ambiente

```bash
# Copiar exemplo de configuração Docker
cp .env.docker.example .env

# Editar .env e ajustar as seguintes variáveis CRÍTICAS:
# - APP_KEY (gerar depois)
# - DB_PASSWORD (trocar senha padrão)
# - DB_ROOT_PASSWORD (trocar senha padrão)
# - REDIS_PASSWORD (trocar senha padrão)
# - GEMINI_API_KEY (sua chave da API Gemini)
# - META_APP_ID, META_APP_SECRET (credenciais Meta)
# - WHATSAPP_* (configurações WhatsApp Business)
```

### 3. Build das Imagens

**Desenvolvimento:**
```bash
docker-compose -f docker-compose.yml -f docker-compose.dev.yml build
```

**Produção:**
```bash
docker-compose build
```

### 4. Iniciar Containers

**Desenvolvimento:**
```bash
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

**Produção:**
```bash
docker-compose up -d
```

### 5. Instalação Laravel

```bash
# Gerar APP_KEY
docker-compose exec app php artisan key:generate

# Executar migrations
docker-compose exec app php artisan migrate

# Executar seeders
docker-compose exec app php artisan db:seed

# Criar storage link
docker-compose exec app php artisan storage:link

# Cache de configurações (produção apenas)
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 6. Verificar Status

```bash
# Ver status de todos os containers
docker-compose ps

# Ver logs
docker-compose logs -f

# Ver logs de um container específico
docker-compose logs -f app
```

---

## 🌍 Ambientes

### Desenvolvimento (`docker-compose.dev.yml`)

**Portas Expostas:**
- `8000`: Aplicação Laravel
- `8080`: phpMyAdmin (DB management)
- `8025`: Mailhog (email testing)
- `8081`: Redis Commander (Redis management)
- `5173`: Vite dev server (hot reload)

**Features:**
- ✅ Xdebug habilitado (porta 9003)
- ✅ Hot reload de código (volumes montados)
- ✅ Logs detalhados (LOG_LEVEL=debug)
- ✅ Ferramentas de desenvolvimento (Mailhog, phpMyAdmin)
- ✅ Mailhog captura todos os emails

**Iniciar:**
```bash
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

**Acessar Ferramentas:**
- App: http://localhost:8000
- phpMyAdmin: http://localhost:8080 (user: `crm_user`, senha: ver `.env`)
- Mailhog: http://localhost:8025
- Redis Commander: http://localhost:8081

### Produção (`docker-compose.yml`)

**Features:**
- ✅ Imagens otimizadas (multi-stage build)
- ✅ OPcache + JIT habilitado
- ✅ Sem ferramentas de debug
- ✅ Logs estruturados
- ✅ Health checks em todos os serviços
- ✅ Resource limits configurados
- ✅ Nginx gzip compression
- ✅ Security headers

**Iniciar:**
```bash
docker-compose up -d
```

**Verificar Health:**
```bash
# Todos os containers devem mostrar "healthy"
docker-compose ps

# Testar health endpoint
curl http://localhost:8000/health
```

---

## 🛠️ Comandos Úteis

### Gerenciamento de Containers

```bash
# Iniciar todos os serviços
docker-compose up -d

# Parar todos os serviços
docker-compose down

# Parar e remover volumes (⚠️ apaga dados do banco!)
docker-compose down -v

# Reiniciar um serviço específico
docker-compose restart app

# Ver logs em tempo real
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f app

# Executar comando em um container
docker-compose exec app bash

# Ver estatísticas de recursos
docker stats
```

### Artisan Commands

```bash
# Executar comando artisan
docker-compose exec app php artisan <command>

# Exemplos:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan queue:work
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list
docker-compose exec app php artisan tinker
```

### Composer & NPM

```bash
# Composer
docker-compose exec app composer install
docker-compose exec app composer update
docker-compose exec app composer require vendor/package

# NPM (se necessário rodar no container)
docker-compose exec app npm install
docker-compose exec app npm run dev
docker-compose exec app npm run build
```

### Database Management

```bash
# Backup do banco
docker-compose exec db mysqldump -u root -p crm_makin > backup.sql

# Restore do banco
docker-compose exec -T db mysql -u root -p crm_makin < backup.sql

# Acesso MySQL CLI
docker-compose exec db mysql -u root -p

# Ver tabelas
docker-compose exec db mysql -u root -p -e "SHOW TABLES FROM crm_makin;"
```

### Queue Management

```bash
# Ver status das queues
docker-compose exec app php artisan queue:work --once

# Limpar queues
docker-compose exec app php artisan queue:clear redis

# Ver failed jobs
docker-compose exec app php artisan queue:failed

# Retry failed job
docker-compose exec app php artisan queue:retry <job-id>

# Retry all failed jobs
docker-compose exec app php artisan queue:retry all
```

### Redis Management

```bash
# Redis CLI
docker-compose exec redis redis-cli -a ${REDIS_PASSWORD}

# Ver todas as keys
docker-compose exec redis redis-cli -a ${REDIS_PASSWORD} KEYS '*'

# Limpar cache
docker-compose exec app php artisan cache:clear

# Limpar tudo do Redis
docker-compose exec redis redis-cli -a ${REDIS_PASSWORD} FLUSHALL
```

### Tests

```bash
# Executar todos os tests
docker-compose exec app php artisan test

# Executar test específico
docker-compose exec app php artisan test --filter=TestName

# Com coverage
docker-compose exec app php artisan test --coverage

# PHPUnit direto
docker-compose exec app ./vendor/bin/phpunit

# Larastan (análise estática)
docker-compose exec app ./vendor/bin/phpstan analyse
```

---

## 🐛 Troubleshooting

### Container não inicia

**Problema:** Container fica reiniciando constantemente

```bash
# Ver logs detalhados
docker-compose logs --tail=100 app

# Verificar se as portas estão em uso
netstat -tuln | grep -E '(8000|3306|6379)'

# Verificar health check
docker inspect <container-id> | grep -A 10 Health
```

**Soluções:**
- Verificar se `.env` está configurado corretamente
- Verificar se as portas não estão em uso por outro processo
- Verificar permissões de arquivos (storage, bootstrap/cache)

### Permissões de arquivos

**Problema:** Erro "Permission denied" ao escrever em storage/logs

```bash
# Ajustar permissões (dentro do container)
docker-compose exec app chown -R laravel:laravel /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

### Banco de dados não conecta

**Problema:** SQLSTATE[HY000] [2002] Connection refused

```bash
# Verificar se MariaDB está rodando
docker-compose ps db

# Ver logs do MariaDB
docker-compose logs db

# Testar conexão do container app
docker-compose exec app ping -c 3 db

# Verificar variáveis de ambiente
docker-compose exec app env | grep DB_
```

**Soluções:**
- Verificar se `DB_HOST=db` no `.env`
- Aguardar 30 segundos após `docker-compose up` (MariaDB precisa inicializar)
- Verificar se senhas no `.env` correspondem ao `docker-compose.yml`

### Redis não conecta

**Problema:** Connection to Redis failed

```bash
# Verificar Redis
docker-compose ps redis

# Testar conexão
docker-compose exec redis redis-cli -a ${REDIS_PASSWORD} ping

# Ver logs
docker-compose logs redis
```

### OPcache não funciona

**Problema:** Mudanças no código não aparecem

```bash
# Limpar OPcache
docker-compose exec app php -r "opcache_reset();"

# Ou reiniciar PHP-FPM
docker-compose restart app

# Em desenvolvimento, desabilitar OPcache
# Editar docker/php/opcache.ini e set opcache.enable=0
```

### Xdebug não conecta (Desenvolvimento)

```bash
# Verificar se Xdebug está ativo
docker-compose exec app php -v | grep Xdebug

# Ver configuração
docker-compose exec app php --ini | grep xdebug

# Verificar variável de ambiente
docker-compose exec app env | grep XDEBUG
```

**Soluções:**
- Verificar se está usando `docker-compose.dev.yml`
- Configurar IDE para escutar na porta 9003
- Verificar se `host.docker.internal` resolve para o host

### Assets não compilam

```bash
# Rebuild dos assets
docker-compose exec app npm run build

# Ver logs de build
docker-compose logs app | grep -i vite

# Verificar se node_modules existe
docker-compose exec app ls -la /var/www/html/node_modules
```

---

## ⚡ Performance

### Otimizações Implementadas

**PHP:**
- ✅ OPcache com JIT (100MB buffer)
- ✅ Realpath cache otimizado
- ✅ Memory limit: 256MB
- ✅ FPM pool otimizado (pm.dynamic)

**MariaDB:**
- ✅ InnoDB buffer pool: 512MB
- ✅ Max connections: 200
- ✅ Query cache desabilitado (MariaDB 11.2)
- ✅ Binary logging para backups

**Redis:**
- ✅ Maxmemory: 256MB
- ✅ Eviction policy: allkeys-lru
- ✅ Persistent (RDB + AOF)

**Nginx:**
- ✅ Gzip compression (level 6)
- ✅ Static files cache (1 ano)
- ✅ Keepalive connections
- ✅ FastCGI buffering otimizado

### Monitoramento

```bash
# CPU e memória dos containers
docker stats

# Top processes no container
docker-compose exec app top

# Ver conexões MySQL ativas
docker-compose exec db mysql -u root -p -e "SHOW PROCESSLIST;"

# Ver uso de memória do Redis
docker-compose exec redis redis-cli -a ${REDIS_PASSWORD} INFO memory

# Ver hit rate do OPcache
docker-compose exec app php -r "print_r(opcache_get_status());"
```

### Tuning Recomendações

**Para tráfego alto (>1000 req/min):**

```yaml
# docker-compose.yml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 1G
      replicas: 3  # Escalar horizontalmente

  db:
    deploy:
      resources:
        limits:
          cpus: '4.0'
          memory: 2G
    environment:
      - MARIADB_INNODB_BUFFER_POOL_SIZE=1G
```

---

## 🔒 Segurança

### Checklist de Segurança

**Antes de ir para produção:**

- [ ] Trocar TODAS as senhas padrão no `.env`
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Gerar novo `APP_KEY`
- [ ] Configurar CORS adequadamente
- [ ] Habilitar HTTPS (adicionar certificado SSL)
- [ ] Remover/desabilitar ferramentas de dev (phpMyAdmin, Mailhog)
- [ ] Configurar backups automatizados
- [ ] Implementar rate limiting
- [ ] Revisar permissões de arquivo
- [ ] Habilitar logs de auditoria
- [ ] Configurar firewall (permitir apenas portas necessárias)
- [ ] Implementar monitoring (Prometheus, Grafana)

### Hardening

**Nginx:**
```nginx
# Adicionar em docker/nginx/conf.d/default.conf
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
```

**Docker:**
```yaml
# docker-compose.yml
services:
  app:
    security_opt:
      - no-new-privileges:true
    read_only: true
    tmpfs:
      - /tmp
      - /var/tmp
```

### Backup

```bash
# Script de backup completo
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups"

# Backup MariaDB
docker-compose exec -T db mysqldump -u root -p${DB_ROOT_PASSWORD} --all-databases > ${BACKUP_DIR}/db_${DATE}.sql

# Backup Redis
docker-compose exec -T redis redis-cli -a ${REDIS_PASSWORD} BGSAVE

# Backup arquivos
tar -czf ${BACKUP_DIR}/storage_${DATE}.tar.gz storage/app

# Limpar backups antigos (>7 dias)
find ${BACKUP_DIR} -type f -mtime +7 -delete
```

---

## 📚 Referências

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
- [MariaDB 11.2 Documentation](https://mariadb.com/kb/en/documentation/)
- [Redis Documentation](https://redis.io/documentation)
- [Nginx Documentation](https://nginx.org/en/docs/)

---

## 🆘 Suporte

Para problemas ou dúvidas:

1. Verificar [Troubleshooting](#troubleshooting) acima
2. Ver logs: `docker-compose logs -f`
3. Verificar health: `docker-compose ps`
4. Consultar documentação oficial do Laravel/Docker

---
