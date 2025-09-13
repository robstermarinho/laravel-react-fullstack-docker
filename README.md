# Laravel API + React Frontend

A full-stack application with a Laravel API backend and React TypeScript frontend, containerized with Docker for easy development and deployment.

## 🏗️ Architecture

- **Backend**: Laravel 12.x (PHP 8.2+) - REST API
- **Frontend**: React 19.x with TypeScript and Vite
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose

## 📋 Prerequisites

- [Docker](https://www.docker.com/get-started) and Docker Compose
- [Git](https://git-scm.com/)

## 🚀 Quick Start

### Initial Setup (First Time)

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd laravel-api-react
   ```

2. **Start and build the containers**

   ```bash
   docker compose up -d --build
   ```

3. **Prepare the backend**

   ```bash
   # Copy environment file
   docker compose exec api cp .env.example .env
   
   # Install PHP dependencies
   docker compose exec api composer install
   
   # Generate application key
   docker compose exec api php artisan key:generate
   
   # Run database migrations
   docker compose exec api php artisan migrate
   ```

4. **Access the application**
   - **Frontend**: <http://localhost:5173>
   - **API**: <http://localhost:8015>
   - **Database**: localhost:3306

### Daily Development

For subsequent runs, simply use:

```bash
docker compose up -d
```

## 🐳 Docker Services

| Service | Container Name | Port | Description |
|---------|---------------|------|-------------|
| `nginx` | `nginx` | 8015 | Nginx reverse proxy for Laravel API |
| `api` | `api` | - | Laravel PHP-FPM application |
| `mysql` | `mysql` | 3306 | MySQL database server |
| `web` | `web` | 5173 | React development server with Vite |

## 🛠️ Development Commands

### Backend (Laravel)

```bash
# Access the API container
docker compose exec api bash

# Run Artisan commands
docker compose exec api php artisan <command>

# Install/update PHP dependencies
docker compose exec api composer install
docker compose exec api composer update

# Run tests
docker compose exec api php artisan test

# Clear caches
docker compose exec api php artisan cache:clear
docker compose exec api php artisan config:clear
docker compose exec api php artisan route:clear
```

### Frontend (React)

```bash
# Access the frontend container
docker compose exec web bash

# Install/update Node dependencies
docker compose exec web npm install
docker compose exec web npm update

# Run linting
docker compose exec web npm run lint

# Build for production
docker compose exec web npm run build
```

### Database

```bash
# Access MySQL directly
docker compose exec mysql mysql -u laravel -p laravel

# Create database backup
docker compose exec mysql mysqldump -u laravel -p laravel > backup.sql

# Restore database backup
docker compose exec -i mysql mysql -u laravel -p laravel < backup.sql
```

## 🔧 Configuration

### Environment Variables

The project uses the following default configuration:

- **Database**: `laravel` (user: `laravel`, password: `secret`)
- **API URL**: `http://localhost:8015`
- **Frontend URL**: `http://localhost:5173`

To customize these values, create a `.env` file in the root directory:

```env
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Frontend API Configuration

The frontend is configured to communicate with the API via the `VITE_API_URL` environment variable, which defaults to `http://localhost:8015`.

## 📁 Project Structure

```
laravel-api-react/
├── backend/               # Laravel API application
│   ├── app/              # Application logic
│   ├── config/           # Configuration files
│   ├── database/         # Migrations, seeders, factories
│   ├── routes/           # API routes
│   └── ...
├── frontend/             # React TypeScript application
│   ├── src/              # Source code
│   ├── public/           # Static assets
│   └── ...
├── docker/               # Docker configuration files
│   └── backend/
│       └── nginx.conf    # Nginx configuration
├── docker-compose.yml    # Docker Compose configuration
└── README.md            # This file
```

## 🔍 Troubleshooting

### Common Issues

1. **Port conflicts**: If ports 3306, 5173, or 8015 are in use, stop the conflicting services or modify the ports in `docker-compose.yml`

2. **Permission issues**: On Linux/macOS, you might need to adjust file permissions:

   ```bash
   sudo chown -R $USER:$USER backend/ frontend/
   ```

3. **Database connection issues**: Ensure the MySQL container is healthy:

   ```bash
   docker compose ps
   docker compose logs mysql
   ```

4. **Container build issues**: Clean and rebuild containers:

   ```bash
   docker compose down
   docker compose up -d --build --force-recreate
   ```

### Logs

View logs for specific services:

```bash
# API logs
docker compose logs api

# Frontend logs
docker compose logs web

# Database logs
docker compose logs mysql

# All logs
docker compose logs
```

## 🧪 Testing

### Backend Tests

```bash
# Run all tests using SQLite in-memory database (recommended)
docker compose exec api ./run-tests.sh

# Run specific test
docker compose exec api ./run-tests.sh --filter=AuthControllerTest

# Run tests with verbose output
docker compose exec api ./run-tests.sh --verbose

# Alternative: Run tests with explicit environment variables
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: api php artisan test
```

**Note**: The `./run-tests.sh` script is recommended as it properly configures the testing environment with SQLite in-memory database for faster and isolated test execution.

### Frontend Tests (Watch mode)

```bash
docker compose exec -it web npm test
```

## 🚢 Production Deployment

For production deployment, consider:

1. **Environment Configuration**: Set appropriate environment variables for production
2. **Database**: Use a managed database service or properly configured database server
3. **SSL/TLS**: Configure HTTPS with proper certificates
4. **Build Optimization**: Use production builds for the frontend
5. **Security**: Review and implement security best practices

## 🔧 Makefile Commands (Optional)

This project includes a comprehensive Makefile that provides shortcuts for common development tasks. Instead of typing long Docker Compose commands, you can use short, memorable make commands.

### Quick Start with Makefile

```bash
# Complete project setup from scratch
git clone <repository-url>
cd laravel-api-react
make setup                      # Equivalent to all manual setup steps above

# Start development environment
make up                         # Interactive mode
make up-d                       # Detached mode
```

### Available Commands

Run `make help` to see all available commands:

```bash
make help
```

### Environment Management

```bash
make up                         # Start all services (interactive)
make up-d                       # Start all services (detached)
make setup                      # Complete first-time setup
make install                    # Install all dependencies
make clean                      # Clean up containers and volumes
```

### Backend/Laravel Commands

```bash
# Composer package management
make composer-add PACKAGE=vendor/package        # Add production dependency
make composer-add-dev PACKAGE=vendor/package    # Add development dependency
make composer-update                             # Update all dependencies
make composer-remove PACKAGE=vendor/package     # Remove dependency
make composer-show                               # Show installed packages

# Laravel Artisan commands
make artisan CMD='migrate'                       # Run any artisan command
make migrate                                     # Run database migrations
make seed                                        # Run database seeders
make fresh                                       # Fresh migrate with seeding

# Testing and code quality
make test                                        # Run PHPUnit tests
make lint                                        # Check code style (PHP CS Fixer)
make format                                      # Fix code style (PHP CS Fixer)
```

### Frontend/React Commands

```bash
make frontend-install                            # Install npm dependencies
make frontend-build                              # Build production assets
make frontend-restart                            # Restart React dev server
```

### Shell Access

```bash
make backend-shell                               # Open bash in API container
make api-shell                                   # Alias for backend-shell
make frontend-shell                              # Open bash in frontend container
make web-shell                                   # Alias for frontend-shell
make mysql-shell                                 # Open MySQL CLI
```

### Service Management

```bash
make backend-restart                             # Restart API container
make frontend-restart                            # Restart frontend container
make nginx-restart                               # Restart nginx container
```

### Makefile Examples

```bash
# Add a new Laravel package
make composer-add PACKAGE=spatie/laravel-permission

# Run migrations and seed database
make fresh

# Run tests and format code
make test
make format

# Open shell to debug
make backend-shell

# Clean everything and start fresh
make clean
make up-d
```

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).