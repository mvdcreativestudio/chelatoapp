# Makefile para ejecutar un proyecto Laravel con Sail

# Variables
APP_NAME = php_chelatoapp
NGINX = nginx_chelatoapp
# MYSQL = mysql_chelatoapp
# PHP_MY_ADMIN = phpmyadmin_chelatoapp
# Desarrollo
dev_install:
	docker compose -f docker-compose.dev.yml up -d --build

dev_install_no_cache:
	docker compose -f docker-compose.dev.yml build --no-cache
	docker compose -f docker-compose.dev.yml up -d --force-recreate

dev_setup:
	# docker compose -f docker-compose.dev.yml exec $(PHP_MY_ADMIN) chmod 777 /sessions
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) bash -c "chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache"
	# docker exec $(APP_NAME) /bin/sh -c "wait-for-it.sh mysql_chelatoapp 3306 echo 'MySQL is up'"
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) composer install
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) npm install --legacy-peer-deps
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan key:generate
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan storage:link

dev_migration:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan migrate:fresh --seed
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan create:modules-permissions
	# docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan migrate --path=database/migrations/custom_migrations

dev_vite:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) npm run dev --watch

dev_migrate:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan migrate

dev_rollback:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan migrate:rollback

dev_permisos:
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) php artisan create:modules-permissions

dev_clear:
	docker compose -f docker-compose.dev.yml down

dev_events:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan events:update

dev_optimize:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan optimize:clear

dev_queue:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan queue:work
# Producción

prod_install:
	docker compose -f docker-compose.prod.yml up -d --build

prod_setup:
	# docker compose -f docker-compose.prod.yml exec $(PHP_MY_ADMIN) chmod 777 /sessions
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) bash -c "chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache"
	# docker exec $(APP_NAME) /bin/sh -c "wait-for-it.sh mysql_chelatoapp 3306 echo 'MySQL is up'"
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) composer install --no-dev
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) npm install
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan key:generate
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan storage:link

prod_migration:
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) php artisan migrate:fresh --seed
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) php artisan create:modules-permissions
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) php artisan migrate --path=database/migrations/custom_migrations

prod_clear:
	docker compose -f docker-compose.prod.yml down

prod_migrate:
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) php artisan migrate

prod_permisos:
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) php artisan create:modules-permissions

prod_vite:
	docker compose -f docker-compose.prod.yml exec $(APP_NAME) npm run build


# Comandos para despliegue manual en CPanel
build_vite:
	docker compose -f docker-compose.manual.yml run --rm vite-build

# Comando para ver rutas
dev_routes:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan route:list

# Comandos para los contenedores

# acceso a la terminal de los contenedores
sshphp:
	docker-compose exec $(APP_NAME) bash

# sshmysql:
# 	docker-compose exec $(MYSQL) bash

sshnginx:
	docker-compose exec $(NGINX) bash

# Variables generales
SUPERVISOR_CONF_DIR = /etc/supervisord.d
SUPERVISOR_CMD = sudo supervisorctl
PROJECT_NAME = chelatoapp
PROJECT_CONF = $(SUPERVISOR_CONF_DIR)/$(PROJECT_NAME).conf
WORKER_LOG = /home/chelatouy/public_html/storage/logs/worker.log

# Crear un archivo de configuración para Supervisor
create-conf:
	@echo "Creando configuración para Supervisor..."
	@sudo bash -c "printf '[program:$(PROJECT_NAME)-worker]\nprocess_name=%(program_name)s_%(process_num)02d\ncommand=php /home/chelatouy/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=3600\nautostart=true\nautorestart=true\nuser=chelatouy\nnumprocs=1\nredirect_stderr=true\nstdout_logfile=$(WORKER_LOG)\nstopwaitsecs=3600\n' > $(PROJECT_CONF)"

# Recargar configuraciones en Supervisor
reload-supervisor:
	@echo "Recargando configuraciones de Supervisor..."
	@$(SUPERVISOR_CMD) reread
	@$(SUPERVISOR_CMD) update

# Iniciar el worker del proyecto
start-worker:
	@echo "Iniciando el worker para $(PROJECT_NAME)..."
	@$(SUPERVISOR_CMD) start $(PROJECT_NAME)-worker:*

# Detener el worker del proyecto
stop-worker:
	@echo "Deteniendo el worker para $(PROJECT_NAME)..."
	@$(SUPERVISOR_CMD) stop $(PROJECT_NAME)-worker:*

# Reiniciar el worker del proyecto
restart-worker:
	@echo "Reiniciando el worker para $(PROJECT_NAME)..."
	@$(SUPERVISOR_CMD) restart $(PROJECT_NAME)-worker:*

# Verificar el estado de los procesos
status:
	@echo "Verificando el estado de los workers..."
	@$(SUPERVISOR_CMD) status

# Verificar los logs del worker
logs:
	@echo "Mostrando los logs del worker..."
	@tail -f $(WORKER_LOG)
