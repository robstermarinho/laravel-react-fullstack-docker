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
   docker compose exec api_app cp .env.example .env
   
   # Install PHP dependencies
   docker compose exec api_app composer install
   
   # Generate application key
   docker compose exec api_app php artisan key:generate
   
   # Run database migrations
   docker compose exec api_app php artisan migrate
   ```

4. **Access the application**
   - **Frontend**: http://localhost:5173
   - **API**: http://localhost:8015
   - **Database**: localhost:3306

### Daily Development

For subsequent runs, simply use:
```bash
docker compose up -d
```

## 🐳 Docker Services

| Service | Container Name | Port | Description |
|---------|---------------|------|-------------|
| `api_nginx` | `api_nginx` | 8015 | Nginx reverse proxy for Laravel API |
| `api_app` | `api_app` | - | Laravel PHP-FPM application |
| `mysql` | `api_mysql` | 3306 | MySQL database server |
| `web` | `web_frontend` | 5173 | React development server with Vite |

## 🛠️ Development Commands

### Backend (Laravel)

```bash
# Access the API container
docker compose exec api_app bash

# Run Artisan commands
docker compose exec api_app php artisan <command>

# Install/update PHP dependencies
docker compose exec api_app composer install
docker compose exec api_app composer update

# Run tests
docker compose exec api_app php artisan test

# Clear caches
docker compose exec api_app php artisan cache:clear
docker compose exec api_app php artisan config:clear
docker compose exec api_app php artisan route:clear
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
docker compose logs api_app

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
docker compose exec api_app php artisan test
```

### Frontend Tests
```bash
docker compose exec web npm test
```

## 🚢 Production Deployment

For production deployment, consider:

1. **Environment Configuration**: Set appropriate environment variables for production
2. **Database**: Use a managed database service or properly configured database server
3. **SSL/TLS**: Configure HTTPS with proper certificates
4. **Build Optimization**: Use production builds for the frontend
5. **Security**: Review and implement security best practices

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

If you encounter any issues or have questions, please [open an issue](../../issues) on GitHub.