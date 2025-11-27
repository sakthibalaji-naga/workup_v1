# WorkUp - Docker PHP Development Environment

A complete Docker setup for PHP 8.1+ development with MySQL and phpMyAdmin, optimized for Windows WSL.

## 📋 Requirements

- Docker Desktop for Windows with WSL 2 backend
- WSL 2 (Ubuntu recommended)
- Git (optional)

## 🚀 Quick Start

### 1. Start Docker Containers

Open PowerShell or WSL terminal in the project directory and run:

```bash
docker-compose up -d
```

This will start all services in the background:
- **PHP 8.1-FPM** - PHP processor
- **Nginx** - Web server
- **MySQL 8.0** - Database server
- **phpMyAdmin** - Database management interface

### 2. Access Your Application

- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### 3. Add Your Application

Place your PHP application files in the `app/` directory. The sample `index.php` file can be replaced with your application code.

## 🗄️ Database Configuration

### Default Credentials

- **Host**: `mysql` (when connecting from PHP)
- **Database**: `workup_db`
- **Username**: `workup_user`
- **Password**: `workup_pass`
- **Root Password**: `root_password`

### phpMyAdmin Access

- **URL**: http://localhost:8081
- **Username**: `root`
- **Password**: `root_password`

### Connecting from PHP

```php
<?php
$host = 'mysql';
$db = 'workup_db';
$user = 'workup_user';
$pass = 'workup_pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
```

## 🛠️ Docker Commands

### Start containers
```bash
docker-compose up -d
```

### Stop containers
```bash
docker-compose down
```

### View logs
```bash
docker-compose logs -f
```

### Restart containers
```bash
docker-compose restart
```

### Rebuild containers (after Dockerfile changes)
```bash
docker-compose up -d --build
```

### Access PHP container shell
```bash
docker exec -it workup_php bash
```

### Access MySQL container shell
```bash
docker exec -it workup_mysql mysql -u root -p
```

## 📁 Project Structure

```
workup/
├── app/                    # Your PHP application files go here
│   └── index.php          # Sample index file
├── docker/
│   ├── nginx/
│   │   └── default.conf   # Nginx configuration
│   ├── php/
│   │   └── php.ini        # PHP configuration
│   └── mysql/
│       └── my.cnf         # MySQL configuration
├── docker-compose.yml     # Docker services configuration
├── Dockerfile             # PHP container definition
├── .env.example           # Environment variables template
└── README.md              # This file
```

## 🔧 Configuration

### PHP Settings

Edit `docker/php/php.ini` to customize PHP settings:
- Upload size limits
- Memory limits
- Timezone
- Error reporting

### Nginx Settings

Edit `docker/nginx/default.conf` to customize web server settings:
- Server name
- Root directory
- URL rewrites

### MySQL Settings

Edit `docker/mysql/my.cnf` to customize database settings:
- Character set
- Performance tuning
- Logging

After changing configuration files, restart the containers:
```bash
docker-compose restart
```

## 📦 Installed PHP Extensions

- pdo_mysql
- mysqli
- mbstring
- gd
- zip
- exif
- pcntl
- bcmath

## 🐛 Troubleshooting

### Containers won't start
```bash
# Check if ports are already in use
netstat -ano | findstr :8080
netstat -ano | findstr :8081
netstat -ano | findstr :3306

# View container logs
docker-compose logs
```

### Permission issues in WSL
```bash
# Fix permissions
sudo chown -R $USER:$USER app/
```

### Database connection fails
- Ensure MySQL container is running: `docker ps`
- Check MySQL logs: `docker-compose logs mysql`
- Verify credentials in your PHP code match `.env.example`

### Reset everything
```bash
# Stop and remove all containers, networks, and volumes
docker-compose down -v

# Start fresh
docker-compose up -d --build
```

## 🔐 Security Notes

**Important**: The default passwords are for development only. For production:

1. Change all default passwords
2. Use environment variables for sensitive data
3. Restrict database access
4. Enable HTTPS
5. Update `php.ini` to disable error display

## 📝 Notes for WSL Users

- Project files should be in the WSL filesystem for better performance
- If your project is on Windows drive (e.g., `/mnt/d/`), consider moving it to WSL home directory
- Use WSL terminal for Docker commands for better compatibility

## 🆘 Support

For issues or questions:
1. Check Docker logs: `docker-compose logs`
2. Verify Docker Desktop is running with WSL 2 backend
3. Ensure WSL 2 is properly configured

## 📄 License

This Docker setup is free to use for your projects.
