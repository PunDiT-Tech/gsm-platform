# Pre-launch Checklist

Run top-to-bottom before going live. Each item has the command or file to use. Full details in `DEPLOYMENT.md` and `NOVICE-DEPLOYMENT-GUIDE.md`.

## 1. Server
- [ ] Ubuntu server provisioned, `apt update` run.
- [ ] Packages installed: `nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-intl php8.2-zip php8.2-bcmath composer unzip supervisor redis-server`.
- [ ] PHP GD extension installed (enables WebP image conversion): `sudo apt install -y php8.2-gd` (or as enabled).
- [ ] Code cloned to `/var/www/gsm-platform` and user owns it.

## 2. Application config
- [ ] `composer install --no-dev --optimize-autoloader`.
- [ ] `npm ci && npm run build`.
- [ ] `cp .env.production.example .env` (not `.env.example`).
- [ ] Edit `.env`:
  - `APP_URL=https://your-domain.com`
  - DB host/name/user/password (create the DB/user first, section 3)
  - `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
  - `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID` (optional)
  - `SESSION_DOMAIN` = your domain
- [ ] `php artisan key:generate` (do NOT reuse the dev key).

## 3. Database
- [ ] MySQL database + user created (see DEPLOYMENT.md §3).
- [ ] `php artisan migrate --seed` — creates tables, roles/permissions, payment methods, and the initial admin.
- [ ] Initial admin login: `admin@gsmplatform.test` / `AdminPass1` — **change this password immediately** after first login, and enable staff 2FA.
- [ ] `php artisan storage:link` (if public storage symlink is used).

## 4. Permissions & background jobs
- [ ] `chown -R www-data:www-data storage bootstrap/cache`; `chmod -R 775 storage bootstrap/cache`.
- [ ] Queue worker running under Supervisor or systemd (`gsm-queue`), verified `active (running)`.
- [ ] Cron line added: `* * * * * cd /var/www/gsm-platform && php artisan schedule:run >> /dev/null 2>&1`.
- [ ] Scheduler heartbeat visible in Admin → System health (runs within 10 min).

## 5. Web server (Nginx)
- [ ] `cp deploy/nginx-gsm-platform.conf /etc/nginx/sites-available/gsm-platform`.
- [ ] Edit: `server_name`, `root` path, `ssl_certificate` paths, PHP-FPM socket (`php8.2`).
- [ ] Enable site: `ln -s ... /etc/nginx/sites-enabled/`; `nginx -t && systemctl reload nginx`.
- [ ] HTTPS obtained: `certbot --nginx -d your-domain.com`; `certbot renew --dry-run` passes.

## 6. Production cache
- [ ] `php artisan config:cache`, `route:cache`, `view:cache`, `php artisan optimize`.
- [ ] Verify `APP_DEBUG` stays `false` and `SESSION_SECURE_COOKIE=true` (both already default in `.env.production.example`).

## 7. Verification
- [ ] `curl -I https://your-domain.com/up` returns 200.
- [ ] Admin → System health: DB/storage/queue/cache/scheduler CONNECTED; mail/telegram probes report sensibly; no `WARNING` for app_debug/secure_cookie.
- [ ] HTTP → HTTPS redirect works; security headers present (`curl -I` shows `X-Frame-Options`, `X-Content-Type-Options`, etc.).
- [ ] Full walkthrough on the live site:
  1. Register a customer, verify email, place an order, view tracking.
  2. Admin sees the order, verifies payment, sets status, adds a result, marks COMPLETED.
  3. Customer sees the new status and result; email/Telegram notifications arrive.

## 8. Backups (day one)
- [ ] Weekly cron: `mysqldump ... | gzip` + `tar` of `storage/` (see NOVICE-DEPLOYMENT-GUIDE §Part N).
- [ ] Backups copied off-server; a restore has been tested once.

## 9. Security final pass
- [ ] Admin password changed; staff 2FA enabled; DB passwords strong and stored in a password manager.
- [ ] `.env` not committed or uploaded; no secrets in the repo.
- [ ] `APP_DEBUG=false` confirmed in the live `.env`.

## Rollback note
Keep the previous code tag + database dump. On failure: restore dump, redeploy old tag, `php artisan migrate` re-verified, restart queue.
