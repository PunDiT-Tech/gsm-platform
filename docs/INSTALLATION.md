# Installation Guide

## Prerequisites

- Ubuntu 22.04+ (or local dev: Windows XAMPP works too)
- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `xml`, `curl`, `intl`, `zip`, `bcmath`
- Composer 2
- MySQL 8+ / MariaDB
- Node.js 20+ (build frontend)
- Git

## Steps

1. Clone the repository.
2. `composer install --no-dev --optimize-autoloader` (dev: `composer install`)
3. `npm install && npm run build`
4. `cp .env.example .env`
5. `php artisan key:generate`
6. Edit `.env` — set `APP_URL`, `APP_ENV`, DB credentials, `MAIL_*`, and optionally Telegram config.
7. Create the database: `CREATE DATABASE gsm_service_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
8. `php artisan migrate --seed`
9. `php artisan storage:link` (public assets)
10. `php artisan serve` for local development, or follow `docs/DEPLOYMENT.md` for production.

## Seeders

- `RolePermissionSeeder` — roles and permissions (required).
- `PlatformSeeder` — default SUPER_ADMIN admin account and payment methods.
- `DemoContentSeeder` — sample categories/services/content (dev only; safe to skip in production by editing `DatabaseSeeder`).

## Permissions for storage

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```
