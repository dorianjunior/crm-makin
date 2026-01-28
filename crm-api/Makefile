# ==============================================================================
# Makefile for CRM Makin - Docker Development
# ==============================================================================
# Comandos úteis para gerenciar o ambiente Docker
# Uso: make <comando>
# ==============================================================================

.PHONY: help build up down restart logs ps shell test clean

# Variáveis
DOCKER_COMPOSE = docker-compose
DOCKER_COMPOSE_DEV = docker-compose -f docker-compose.yml -f docker-compose.dev.yml
DOCKER_EXEC = docker-compose exec app
DOCKER_EXEC_ROOT = docker-compose exec -u root app

# Cores para output
GREEN = \033[0;32m
YELLOW = \033[1;33m
RED = \033[0;31m
NC = \033[0m # No Color

##@ Help

help: ## Mostra esta mensagem de ajuda
	@echo '$(GREEN)CRM Makin - Comandos Docker$(NC)'
	@echo ''
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make $(YELLOW)<target>$(NC)\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  $(GREEN)%-15s$(NC) %s\n", $$1, $$2 } /^##@/ { printf "\n$(YELLOW)%s$(NC)\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Development

dev: ## Iniciar ambiente de desenvolvimento (com ferramentas)
	@echo "$(GREEN)🚀 Iniciando ambiente de desenvolvimento...$(NC)"
	$(DOCKER_COMPOSE_DEV) up -d
	@echo "$(GREEN)✅ Ambiente iniciado!$(NC)"
	@echo "$(YELLOW)Aplicação: http://localhost:8000$(NC)"
	@echo "$(YELLOW)phpMyAdmin: http://localhost:8080$(NC)"
	@echo "$(YELLOW)Mailhog: http://localhost:8025$(NC)"
	@echo "$(YELLOW)Redis Commander: http://localhost:8081$(NC)"

dev-build: ## Build e inicia ambiente de desenvolvimento
	@echo "$(GREEN)🔨 Building containers...$(NC)"
	$(DOCKER_COMPOSE_DEV) build --no-cache
	@echo "$(GREEN)🚀 Iniciando containers...$(NC)"
	$(DOCKER_COMPOSE_DEV) up -d

dev-stop: ## Para ambiente de desenvolvimento
	@echo "$(YELLOW)⏸️  Parando ambiente...$(NC)"
	$(DOCKER_COMPOSE_DEV) down

dev-restart: ## Reinicia ambiente de desenvolvimento
	@echo "$(YELLOW)🔄 Reiniciando ambiente...$(NC)"
	$(DOCKER_COMPOSE_DEV) restart

##@ Production

prod: ## Iniciar ambiente de produção
	@echo "$(GREEN)🚀 Iniciando ambiente de produção...$(NC)"
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)✅ Ambiente iniciado!$(NC)"

prod-build: ## Build e inicia ambiente de produção
	@echo "$(GREEN)🔨 Building production containers...$(NC)"
	$(DOCKER_COMPOSE) build --no-cache
	@echo "$(GREEN)🚀 Iniciando containers...$(NC)"
	$(DOCKER_COMPOSE) up -d

prod-stop: ## Para ambiente de produção
	@echo "$(YELLOW)⏸️  Parando ambiente...$(NC)"
	$(DOCKER_COMPOSE) down

##@ Docker Management

build: ## Build das imagens (desenvolvimento)
	@echo "$(GREEN)🔨 Building containers...$(NC)"
	$(DOCKER_COMPOSE_DEV) build

up: ## Sobe os containers (desenvolvimento)
	@echo "$(GREEN)⬆️  Subindo containers...$(NC)"
	$(DOCKER_COMPOSE_DEV) up -d

down: ## Para e remove containers
	@echo "$(RED)⬇️  Parando containers...$(NC)"
	$(DOCKER_COMPOSE_DEV) down

restart: ## Reinicia todos os containers
	@echo "$(YELLOW)🔄 Reiniciando containers...$(NC)"
	$(DOCKER_COMPOSE_DEV) restart

ps: ## Lista containers em execução
	@$(DOCKER_COMPOSE_DEV) ps

logs: ## Mostra logs de todos os containers
	@$(DOCKER_COMPOSE_DEV) logs -f

logs-app: ## Mostra logs do container app
	@$(DOCKER_COMPOSE_DEV) logs -f app

logs-nginx: ## Mostra logs do nginx
	@$(DOCKER_COMPOSE_DEV) logs -f nginx

logs-db: ## Mostra logs do banco de dados
	@$(DOCKER_COMPOSE_DEV) logs -f db

logs-queue: ## Mostra logs do queue worker
	@$(DOCKER_COMPOSE_DEV) logs -f queue-worker

##@ Application

shell: ## Acessa shell do container app
	@$(DOCKER_EXEC) bash

shell-root: ## Acessa shell do container app como root
	@$(DOCKER_EXEC_ROOT) bash

install: ## Instala aplicação (primeira vez)
	@echo "$(GREEN)📦 Instalando dependências...$(NC)"
	$(DOCKER_EXEC) composer install
	@echo "$(GREEN)🔑 Gerando APP_KEY...$(NC)"
	$(DOCKER_EXEC) php artisan key:generate
	@echo "$(GREEN)🗄️  Executando migrations...$(NC)"
	$(DOCKER_EXEC) php artisan migrate
	@echo "$(GREEN)🌱 Executando seeders...$(NC)"
	$(DOCKER_EXEC) php artisan db:seed
	@echo "$(GREEN)🔗 Criando storage link...$(NC)"
	$(DOCKER_EXEC) php artisan storage:link
	@echo "$(GREEN)✅ Instalação completa!$(NC)"

migrate: ## Executa migrations
	@echo "$(GREEN)🗄️  Executando migrations...$(NC)"
	$(DOCKER_EXEC) php artisan migrate

migrate-fresh: ## Recria banco de dados (⚠️ apaga dados!)
	@echo "$(RED)⚠️  ATENÇÃO: Isso vai apagar todos os dados!$(NC)"
	@read -p "Continuar? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(DOCKER_EXEC) php artisan migrate:fresh --seed; \
	fi

seed: ## Executa seeders
	@echo "$(GREEN)🌱 Executando seeders...$(NC)"
	$(DOCKER_EXEC) php artisan db:seed

tinker: ## Abre Laravel Tinker
	@$(DOCKER_EXEC) php artisan tinker

##@ Cache

cache-clear: ## Limpa todos os caches
	@echo "$(YELLOW)🧹 Limpando caches...$(NC)"
	$(DOCKER_EXEC) php artisan cache:clear
	$(DOCKER_EXEC) php artisan config:clear
	$(DOCKER_EXEC) php artisan route:clear
	$(DOCKER_EXEC) php artisan view:clear
	@echo "$(GREEN)✅ Caches limpos!$(NC)"

cache-optimize: ## Otimiza caches (produção)
	@echo "$(GREEN)⚡ Otimizando caches...$(NC)"
	$(DOCKER_EXEC) php artisan config:cache
	$(DOCKER_EXEC) php artisan route:cache
	$(DOCKER_EXEC) php artisan view:cache
	@echo "$(GREEN)✅ Caches otimizados!$(NC)"

opcache-clear: ## Limpa OPcache
	@echo "$(YELLOW)🧹 Limpando OPcache...$(NC)"
	$(DOCKER_EXEC) php -r "opcache_reset();"
	@echo "$(GREEN)✅ OPcache limpo!$(NC)"

##@ Queue

queue: ## Executa queue worker
	@$(DOCKER_EXEC) php artisan queue:work

queue-listen: ## Executa queue em modo listen
	@$(DOCKER_EXEC) php artisan queue:listen

queue-restart: ## Reinicia queue workers
	@echo "$(YELLOW)🔄 Reiniciando queue workers...$(NC)"
	$(DOCKER_EXEC) php artisan queue:restart

queue-failed: ## Lista jobs que falharam
	@$(DOCKER_EXEC) php artisan queue:failed

queue-retry: ## Tenta novamente jobs que falharam
	@$(DOCKER_EXEC) php artisan queue:retry all

##@ Database

db-shell: ## Acessa MySQL shell
	@docker-compose exec db mysql -u root -p

db-backup: ## Faz backup do banco de dados
	@echo "$(GREEN)💾 Fazendo backup do banco...$(NC)"
	@mkdir -p backups
	@docker-compose exec -T db mysqldump -u root -p$${DB_ROOT_PASSWORD} crm_makin > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✅ Backup salvo em backups/$(NC)"

db-restore: ## Restaura backup do banco (usar: make db-restore FILE=backup.sql)
ifndef FILE
	@echo "$(RED)❌ Especifique o arquivo: make db-restore FILE=backups/backup.sql$(NC)"
else
	@echo "$(YELLOW)📥 Restaurando backup...$(NC)"
	@docker-compose exec -T db mysql -u root -p$${DB_ROOT_PASSWORD} crm_makin < $(FILE)
	@echo "$(GREEN)✅ Backup restaurado!$(NC)"
endif

##@ Testing

test: ## Executa todos os testes
	@echo "$(GREEN)🧪 Executando testes...$(NC)"
	$(DOCKER_EXEC) php artisan test

test-coverage: ## Executa testes com coverage
	@echo "$(GREEN)🧪 Executando testes com coverage...$(NC)"
	$(DOCKER_EXEC) php artisan test --coverage

test-filter: ## Executa teste específico (usar: make test-filter FILTER=TestName)
ifndef FILTER
	@echo "$(RED)❌ Especifique o teste: make test-filter FILTER=TestName$(NC)"
else
	@$(DOCKER_EXEC) php artisan test --filter=$(FILTER)
endif

phpstan: ## Executa análise estática (Larastan)
	@echo "$(GREEN)🔍 Executando análise estática...$(NC)"
	$(DOCKER_EXEC) ./vendor/bin/phpstan analyse

pint: ## Executa Laravel Pint (code style)
	@echo "$(GREEN)✨ Executando Laravel Pint...$(NC)"
	$(DOCKER_EXEC) ./vendor/bin/pint

pint-test: ## Testa code style sem modificar
	@echo "$(GREEN)✨ Testando code style...$(NC)"
	$(DOCKER_EXEC) ./vendor/bin/pint --test

##@ Utilities

composer-install: ## Instala dependências Composer
	@$(DOCKER_EXEC) composer install

composer-update: ## Atualiza dependências Composer
	@$(DOCKER_EXEC) composer update

npm-install: ## Instala dependências NPM
	@$(DOCKER_EXEC) npm install

npm-dev: ## Executa Vite dev server
	@$(DOCKER_EXEC) npm run dev

npm-build: ## Builda assets para produção
	@$(DOCKER_EXEC) npm run build

fix-permissions: ## Corrige permissões de arquivos
	@echo "$(YELLOW)🔧 Corrigindo permissões...$(NC)"
	$(DOCKER_EXEC_ROOT) chown -R laravel:laravel /var/www/html/storage
	$(DOCKER_EXEC_ROOT) chown -R laravel:laravel /var/www/html/bootstrap/cache
	$(DOCKER_EXEC_ROOT) chmod -R 775 /var/www/html/storage
	$(DOCKER_EXEC_ROOT) chmod -R 775 /var/www/html/bootstrap/cache
	@echo "$(GREEN)✅ Permissões corrigidas!$(NC)"

health: ## Verifica saúde dos containers
	@echo "$(GREEN)🏥 Verificando saúde dos containers...$(NC)"
	@$(DOCKER_COMPOSE_DEV) ps
	@echo ""
	@echo "$(GREEN)📊 Estatísticas de recursos:$(NC)"
	@docker stats --no-stream

##@ Cleanup

clean: ## Remove containers, volumes e imagens
	@echo "$(RED)🧹 Limpando ambiente Docker...$(NC)"
	@read -p "⚠️  Isso vai remover TODOS os dados. Continuar? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(DOCKER_COMPOSE_DEV) down -v --rmi local; \
		echo "$(GREEN)✅ Ambiente limpo!$(NC)"; \
	fi

clean-volumes: ## Remove apenas volumes (⚠️ apaga dados!)
	@echo "$(RED)⚠️  Removendo volumes (dados serão perdidos!)...$(NC)"
	@read -p "Continuar? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(DOCKER_COMPOSE_DEV) down -v; \
		echo "$(GREEN)✅ Volumes removidos!$(NC)"; \
	fi

prune: ## Remove recursos Docker não utilizados
	@echo "$(YELLOW)🧹 Removendo recursos não utilizados...$(NC)"
	docker system prune -f
	@echo "$(GREEN)✅ Limpeza concluída!$(NC)"

##@ Info

stats: ## Mostra estatísticas de recursos
	@docker stats

inspect-app: ## Inspeciona container app
	@docker inspect crm-makin-app

inspect-db: ## Inspeciona container db
	@docker inspect crm-makin-db

network-ls: ## Lista networks Docker
	@docker network ls | grep crm

volume-ls: ## Lista volumes Docker
	@docker volume ls | grep crm

version: ## Mostra versões dos serviços
	@echo "$(GREEN)📦 Versões:$(NC)"
	@echo "Docker: $$(docker --version)"
	@echo "Docker Compose: $$(docker-compose --version)"
	@echo ""
	@echo "$(GREEN)Container App:$(NC)"
	@$(DOCKER_EXEC) php --version | head -n 1
	@$(DOCKER_EXEC) composer --version
	@echo ""
	@echo "$(GREEN)Container DB:$(NC)"
	@docker-compose exec db mysql --version
	@echo ""
	@echo "$(GREEN)Container Redis:$(NC)"
	@docker-compose exec redis redis-server --version
