.PHONY: up down migrate seed test

up:
	docker compose up -d --build

down:
	docker compose down

migrate:
	docker compose exec app php artisan migrate --seed

test:
	docker compose exec app php artisan test
