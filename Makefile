# Makefile para ejecutar un proyecto Laravel con Sail

# Variables

APP_NAME = php_base
NGINX = nginx_base
# MYSQL = mysql_base
# PHP_MY_ADMIN = phpmyadmin_base

# Desarrollo
dev_install:
	docker compose -f docker-compose.dev.yml up -d --build

dev_install_no_cache:
	docker compose -f docker-compose.dev.yml build --no-cache
	docker compose -f docker-compose.dev.yml up -d --force-recreate

dev_setup:
	# docker compose -f docker-compose.dev.yml exec $(PHP_MY_ADMIN) chmod 777 /sessions
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) bash -c "chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache"
	# docker exec $(APP_NAME) /bin/sh -c "wait-for-it.sh mysql_base 3306 echo 'MySQL is up'"
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

dev_clear:
	docker compose -f docker-compose.dev.yml down

dev_create_migration:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan make:migration $(name)

dev_permissions:
	docker compose -f docker-compose.dev.yml exec $(APP_NAME) php artisan create:modules-permissions

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
	# docker exec $(APP_NAME) /bin/sh -c "wait-for-it.sh mysql_base 3306 echo 'MySQL is up'"
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
