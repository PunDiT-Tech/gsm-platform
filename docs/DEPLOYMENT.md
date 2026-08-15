# Deployment Guide (Ubuntu + Nginx + PHP-FPM)

## 1. Server preparation

```bash
apt update
apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-intl php8.2-zip php8.2-bcmath \
  composer unzip supervisor redis-server
```

## 2. Application deployment

```bash
cd /var/www
git clone <repo> gsm-platform
cd gsm-platform
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.production.example .env
php artisan key:generate
```

> Production env defaults are hardened in `.env.production.example`: `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `LOG_LEVEL=warning`, SMTP mail. Fill in DB credentials, `APP_URL` (https), mail, and Telegram values before deploying.

## 3. Database

```sql
CREATE DATABASE gsm_service_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gsm'@'localhost' IDENTIFIED BY '<strong-password>';
GRANT ALL PRIVILEGES ON gsm_service_platform.* TO 'gsm'@'localhost';
FLUSH PRIVILEGES;
```

Set the credentials in `.env`, then:

```bash
php artisan migrate --seed
php artisan storage:link
```

## 4. Permissions

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

## 5. Queue worker (Supervisor)

`/etc/supervisor/conf.d/gsm-queue.conf`:

```ini
[program:gsm-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gsm-platform/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/gsm-platform/storage/logs/queue.log
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl restart gsm-queue:*
```

## 6. Scheduler (Cron)

```bash
crontab -e
# add:
* * * * * cd /var/www/gsm-platform && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Nginx site

A production-ready template (HTTPS redirect, security headers, asset/image caching) ships in the repo:

```bash
cp deploy/nginx-gsm-platform.conf /etc/nginx/sites-available/gsm-platform
# edit server_name, root path, ssl_certificate paths, and PHP-FPM socket to match your server
ln -s /etc/nginx/sites-available/gsm-platform /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

Minimal HTTP-only version (development):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/gsm-platform/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;
    charset utf-8;
    client_max_body_size 12M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 8. HTTPS (Let's Encrypt)

```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d your-domain.com
```

## 9. Backup

- Database: `mysqldump gsm_service_platform > backup.sql` (schedule nightly).
- Files: back up `storage/app` (private files) and `.env`.
- Test restore regularly.

## 10. Health check

`GET /up` returns a health response. Run `php artisan orders:expire-unpaid` via the scheduler automatically.

## Rollback

- Keep `.env` and database dumps. Deploy new code, run `php artisan migrate`, and restart queue. If a migration fails, restore the previous dump and code tag.
