## Initial Setup - First Time

#### Start and build the containers
```bash
docker compose up -d --build
```

#### Prepare backend
```bash
docker compose exec api_app cp .env.example .env
docker compose exec api_app composer install
docker compose exec api_app php artisan key:generate
docker compose exec api_app php artisan migrate
```
