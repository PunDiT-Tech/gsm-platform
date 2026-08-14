# GSM Service Platform

Production-ready GSM service ordering platform. Customers browse legitimate device repair, diagnostic and maintenance services, submit service requests, make manual payments, upload proof of payment, receive notifications, and track their orders. Administrators manage services, dynamic forms, pricing, payment methods, orders, customers, announcements, homepage content, Telegram notifications, staff permissions and system settings.

## Requirements

- PHP 8.2+
- Composer 2
- MySQL 8+ / MariaDB 10.4+
- Node 20+ (for frontend build)

## Quick start (local)

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
# configure DB credentials in .env
php artisan migrate --seed
php artisan serve
```

Default admin (from seeder): `admin@gsmplatform.test` / `AdminPass1`. Change it immediately.

## Commands

```bash
php artisan orders:expire-unpaid   # expire unpaid orders past deadline
php artisan schedule:work          # run scheduler locally
php artisan queue:work             # process notifications/telegram
```

## Testing

```bash
php artisan test
```

## Architecture

- Clean layering: Controllers → Services → Repositories → Models
- Order data integrity via snapshots (`service_name_snapshot`, `price_snapshot`, `currency_snapshot`)
- Server-side authorization via RBAC roles + permission middleware + audit logging
- Two-factor authentication (TOTP) challenge for all staff with recovery codes
- Private file storage for proofs/results; downloads authorized
- Queued notifications (email + Telegram); failures never roll back DB transactions

## Documentation

- `docs/ARCHITECTURE.md`
- `docs/DATABASE-DIAGRAM.md`
- `docs/ROUTE-MAP.md`
- `docs/PERMISSION-MATRIX.md`
- `docs/STAGED-PLAN.md`
- `docs/INSTALLATION.md`
- `docs/DEPLOYMENT.md`
- `docs/BACKUP-RESTORE.md`
- `docs/TROUBLESHOOTING.md`
- `docs/SECURITY-AUDIT.md`
- `docs/FUNCTIONALITY-AUDIT.md`
- `docs/PERFORMANCE-AUDIT.md`
- `docs/SCALABILITY-AUDIT.md`
- `docs/CODE-QUALITY-AUDIT.md`

## Security notes

- This platform is intended for legitimate GSM repair, diagnostics and authorized maintenance only.
- Do not commit `.env`, real credentials, or payment secrets.
- Require two-factor authentication for every staff account before granting production access (see `/profile/two-factor`).
- Enable HTTPS, queue workers (Supervisor), and the scheduler (Cron) in production.
