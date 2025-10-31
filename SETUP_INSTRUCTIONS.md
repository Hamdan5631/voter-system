# Setup Instructions - Digital Voters List API

## Quick Start Guide

### 1. Install Dependencies
```bash
cd voters-list-api
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voters_list
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Create Database
```bash
mysql -u your_username -p
CREATE DATABASE voters_list;
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Database
```bash
php artisan db:seed
```

This creates:
- **Superadmin:** `admin@voterslist.com` / `password123`
- Sample wards, team leads, booth agents, and workers

### 7. Link Storage
```bash
php artisan storage:link
```

This enables public access to uploaded voter images.

### 8. Start Server
```bash
php artisan serve
```

API is now available at `http://localhost:8000`

## Testing the API

### 1. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@voterslist.com","password":"password123"}'
```

### 2. Use Token in Requests
Copy the token from the login response and use it:
```bash
curl -X GET http://localhost:8000/api/voters \
  -H "Authorization: Bearer {your-token}" \
  -H "Accept: application/json"
```

### 3. Import Postman Collection
1. Open Postman
2. Import `Voters_List_API.postman_collection.json`
3. Login to get token (auto-saved)
4. Test all endpoints

## Running Tests

```bash
php artisan test
```

## Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure production database credentials
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set proper file permissions (storage, bootstrap/cache)
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up SSL certificate
- [ ] Configure CORS if needed
- [ ] Set up queue worker if using queues
- [ ] Set up scheduled tasks (cron for Laravel scheduler)

## Troubleshooting

### Permission Denied Errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Storage Link Issues
```bash
php artisan storage:link
```

### Migration Errors
```bash
php artisan migrate:fresh --seed
```

### Clear Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## Support

Refer to `README.md` and `API_DOCUMENTATION.md` for detailed information.
