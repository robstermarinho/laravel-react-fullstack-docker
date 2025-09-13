.PHONY: help up up-d composer-add composer-add-dev composer-update composer-remove composer-show clean test lint format backend-shell frontend-shell api-shell web-shell frontend-install frontend-build frontend-restart backend-restart nginx-restart mysql-shell artisan migrate seed fresh install setup

# Default target
help:
	@echo "Available commands:"
	@echo ""
	@echo "🚀 Environment:"
	@echo "  up              - Start development environment"
	@echo "  up-d            - Start development environment (detached)"
	@echo "  setup           - Complete project setup (first time)"
	@echo "  install         - Install all dependencies"
	@echo "  clean           - Clean up containers and volumes"
	@echo ""
	@echo "📦 Composer (Backend):"
	@echo "  composer-add    - Add production dependency (usage: make composer-add PACKAGE=package/name)"
	@echo "  composer-add-dev- Add dev dependency (usage: make composer-add-dev PACKAGE=package/name)"
	@echo "  composer-update - Update dependencies"
	@echo "  composer-remove - Remove dependency (usage: make composer-remove PACKAGE=package/name)"
	@echo "  composer-show   - Show installed packages"
	@echo ""
	@echo "🏗️ Laravel (Backend):"
	@echo "  artisan         - Run artisan command (usage: make artisan CMD='migrate')"
	@echo "  migrate         - Run database migrations"
	@echo "  seed            - Run database seeders"
	@echo "  fresh           - Fresh migrate with seeding"
	@echo ""
	@echo "🧪 Testing & Quality:"
	@echo "  test            - Run backend tests"
	@echo "  lint            - Run linting (PHP CS Fixer)"
	@echo "  format          - Format code (PHP CS Fixer)"
	@echo ""
	@echo "🐚 Shells:"
	@echo "  backend-shell   - Open shell in backend container"
	@echo "  api-shell       - Open shell in API container (alias)"
	@echo "  frontend-shell  - Open shell in frontend container"
	@echo "  web-shell       - Open shell in web container (alias)"
	@echo "  mysql-shell     - Open MySQL shell"
	@echo ""
	@echo "⚛️ Frontend:"
	@echo "  frontend-install - Install frontend dependencies"
	@echo "  frontend-build   - Build frontend"
	@echo "  frontend-restart - Restart frontend container"
	@echo ""
	@echo "🔄 Services:"
	@echo "  backend-restart  - Restart backend container"
	@echo "  nginx-restart    - Restart nginx container"

# Development environment
up:
	@echo "🚀 Starting development environment..."
	docker compose up --build

up-d:
	@echo "🚀 Starting development environment (detached)..."
	docker compose up --build -d

# Complete project setup
setup:
	@echo "🎯 Setting up Laravel API + React project..."
	@echo "📦 Starting containers..."
	docker compose up -d --build
	@echo "⏳ Waiting for containers to be ready..."
	sleep 10
	@echo "📝 Setting up Laravel environment..."
	docker compose exec api cp .env.example .env || true
	docker compose exec api composer install
	docker compose exec api php artisan key:generate
	docker compose exec api php artisan migrate
	@echo "📦 Installing frontend dependencies..."
	docker compose exec web npm install
	@echo "✅ Setup complete! Visit http://localhost:5173 (frontend) and http://localhost:8015 (API)"

# Install dependencies
install:
	@echo "📦 Installing all dependencies..."
	docker compose exec api composer install
	docker compose exec web npm install
	@echo "✅ Dependencies installed!"

# Composer commands
composer-add:
	@if [ -z "$(PACKAGE)" ]; then echo "❌ Usage: make composer-add PACKAGE=package/name"; exit 1; fi
	@echo "📦 Adding production dependency: $(PACKAGE)"
	docker compose exec api composer require $(PACKAGE)
	@echo "✅ Dependency $(PACKAGE) added!"

composer-add-dev:
	@if [ -z "$(PACKAGE)" ]; then echo "❌ Usage: make composer-add-dev PACKAGE=package/name"; exit 1; fi
	@echo "🛠️ Adding dev dependency: $(PACKAGE)"
	docker compose exec api composer require --dev $(PACKAGE)
	@echo "✅ Dev dependency $(PACKAGE) added!"

composer-update:
	@echo "🔄 Updating Composer dependencies..."
	docker compose exec api composer update
	@echo "✅ Dependencies updated!"

composer-remove:
	@if [ -z "$(PACKAGE)" ]; then echo "❌ Usage: make composer-remove PACKAGE=package/name"; exit 1; fi
	@echo "🗑️ Removing dependency: $(PACKAGE)"
	docker compose exec api composer remove $(PACKAGE)
	@echo "✅ Dependency $(PACKAGE) removed!"

composer-show:
	@echo "📊 Showing installed packages..."
	docker compose exec api composer show

# Laravel Artisan commands
artisan:
	@if [ -z "$(CMD)" ]; then echo "❌ Usage: make artisan CMD='command'"; exit 1; fi
	@echo "⚡ Running artisan $(CMD)..."
	docker compose exec api php artisan $(CMD)

migrate:
	@echo "📊 Running database migrations..."
	docker compose exec api php artisan migrate

seed:
	@echo "🌱 Running database seeders..."
	docker compose exec api php artisan db:seed

fresh:
	@echo "🔄 Fresh migrating with seeding..."
	docker compose exec api php artisan migrate:fresh --seed

# Testing and code quality
test:
	@echo "🧪 Running backend tests..."
	docker compose exec api php artisan test

lint:
	@echo "🔍 Running PHP CS Fixer (dry-run)..."
	docker compose exec api ./vendor/bin/pint --test

format:
	@echo "✨ Formatting code with PHP CS Fixer..."
	docker compose exec api ./vendor/bin/pint

# Shell access
backend-shell:
	@echo "🐚 Opening shell in backend container..."
	docker compose exec api bash

api-shell:
	@echo "🐚 Opening shell in API container..."
	docker compose exec api bash

frontend-shell:
	@echo "🐚 Opening shell in frontend container..."
	docker compose exec web bash

web-shell:
	@echo "🐚 Opening shell in web container..."
	docker compose exec web bash

mysql-shell:
	@echo "🗄️ Opening MySQL shell..."
	docker compose exec mysql mysql -u $(shell grep DB_USERNAME backend/.env | cut -d '=' -f2 | tr -d '"' || echo "laravel") -p$(shell grep DB_PASSWORD backend/.env | cut -d '=' -f2 | tr -d '"' || echo "secret") $(shell grep DB_DATABASE backend/.env | cut -d '=' -f2 | tr -d '"' || echo "laravel")

# Frontend commands
frontend-install:
	@echo "📦 Installing frontend dependencies..."
	docker compose exec web npm install

frontend-build:
	@echo "🏗️ Building frontend..."
	docker compose exec web npm run build

frontend-restart:
	@echo "🔄 Restarting frontend container..."
	docker compose restart web

# Service restarts
backend-restart:
	@echo "🔄 Restarting backend container..."
	docker compose restart api

nginx-restart:
	@echo "🔄 Restarting nginx container..."
	docker compose restart nginx

# Clean up
clean:
	@echo "🧹 Cleaning up Docker containers and volumes..."
	docker compose down -v
	docker builder prune -f
	@echo "✅ Cleanup complete!"
