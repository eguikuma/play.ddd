.PHONY: up down restart build logs ps \
       backend\:format backend\:format\:fix backend\:test backend\:seed

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build

logs:
	docker compose logs -f

ps:
	docker compose ps

backend\:format:
	docker compose exec app ./vendor/bin/pint --test

backend\:format\:fix:
	docker compose exec app ./vendor/bin/pint

backend\:test:
	docker compose exec app ./vendor/bin/phpunit

backend\:migrate:
	docker compose exec app php artisan migrate

backend\:seed:
	docker compose exec app php artisan db:seed

backend\:fresh:
	docker compose exec app php artisan migrate:fresh --seed
