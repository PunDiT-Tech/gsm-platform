# Backup & Restore Plan

Complements DEPLOYMENT.md. Run a backup before every major production migration.

## What to back up
| Item | Location | Why |
|---|---|---|
| Database | MySQL/MariaDB | All orders, customers, content, settings |
| Private files | `storage/app/private` | Payment proofs, order results, attachments |
| Environment | `.env` | Keys, credentials, payment/Telegram config |
| Code | Git tag/release | Reproducible rollback point |

## Database backup
```bash
# Full dump (nightly recommended)
mysqldump --single-transaction --routines --triggers gsm_service_platform > /backups/gsm_$(date +%F).sql

# Automated: cron entry on the server
0 2 * * * mysqldump --single-transaction gsm_service_platform | gzip > /backups/db_$(date +%%F).sql.gz 2>/dev/null
```

## Files backup
```bash
# Private storage (proofs, results, attachments)
tar -czf /backups/storage_$(date +%F).tar.gz -C /var/www/gsm-platform storage/app

# Uploaded assets under public (if any live uploads)
tar -czf /backups/public_$(date +%F).tar.gz -C /var/www/gsm-platform public/uploads
```

## Environment backup
- Keep a copy of `.env` outside the server (secret manager or password manager).
- Never commit `.env` to git. `.env.example` documents the required variables.

## Restore procedure
```bash
# 1. Restore code (from the git tag used in production)
git checkout <tag>
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 2. Restore environment
cp /backups/.env .env

# 3. Restore files
tar -xzf /backups/storage_$(date +%F).tar.gz -C /var/www/gsm-platform storage/app

# 4. Restore database
mysql -u gsm_user -p gsm_service_platform < /backups/gsm_$(date +%F).sql

# 5. Apply any pending migrations
php artisan migrate --force

# 6. Rebuild caches and restart workers
php artisan config:cache
php artisan route:cache
php artisan queue:restart
systemctl restart php8.x-fpm nginx supervisor
```

## Database rollback procedure
- Laravel migrations are destructive once run; there is no automatic down of irreversible changes.
- To roll back a failed deploy: point the app at the previous database dump (step 4) and the previous code tag (step 1), then restart workers.
- Never run `migrate:fresh` or destructive SQL on the production database.

## Emergency maintenance procedure
1. Announce maintenance (HTTP 503 via `.env` `APP_MAINTENANCE_DRIVER` file mode or a put in `storage/framework/maintenance`).
2. Halt queue workers (`supervisorctl stop all` for the app group) to stop processing.
3. Capture a fresh backup before any repair step.
4. Apply the fix, run migrations, restart queue, remove maintenance mode.

## Verification
- Test restores in staging monthly.
- After restore, verify: admin login works, an order can be viewed, a payment proof downloads, a result file downloads.
- Check `php artisan migrate:status` shows the expected migration state.