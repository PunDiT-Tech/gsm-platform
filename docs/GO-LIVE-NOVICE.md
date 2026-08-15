# Go-live guide for novices

Launch the GSM Service Platform on a fresh Ubuntu server, step by step. If something fails, see `docs/TROUBLESHOOTING.md`. Full server-level details are in `docs/DEPLOYMENT.md`; this guide is the short, copy-paste version.

Before you start, make sure you have:
- A server running Ubuntu (22.04/24.04) with SSH access.
- A domain name pointing to the server (DNS A record), e.g. `your-domain.com`.
- Your code in a git repository you can `git clone`.

---

## Step 1 — Install the server packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-intl php8.2-zip php8.2-bcmath \
  php8.2-gd composer unzip supervisor
```

`php8.2-gd` enables WebP image optimization (without it the app still works, it just skips WebP).

---

## Step 2 — Put the code on the server

```bash
cd /var/www
sudo git clone <your-repo-url> gsm-platform
sudo chown -R $USER:www-data gsm-platform
cd gsm-platform
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

The `npm run build` compiles the frontend assets.

---

## Step 3 — Create the database

```bash
sudo mysql
```

Run these SQL statements (put a strong password where it says `<password>`):

```sql
CREATE DATABASE gsm_service_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gsm'@'localhost' IDENTIFIED BY '<password>';
GRANT ALL PRIVILEGES ON gsm_service_platform.* TO 'gsm'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Step 4 — Configure the app

```bash
cp .env.production.example .env
nano .env
```

Edit these lines (use `Ctrl+W` in nano to search for each one):
- `APP_URL=https://your-domain.com` — use your real domain
- `DB_DATABASE=gsm_service_platform`, `DB_USERNAME=gsm`, `DB_PASSWORD=<the password you set>`
- `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` — your SMTP credentials (e.g. Gmail app password, Mailgun, SendGrid)
- `SESSION_DOMAIN=your-domain.com` — your domain without `https://`
- `TELEGRAM_BOT_TOKEN` / `TELEGRAM_CHAT_ID` — optional, enables Telegram notifications

Save with `Ctrl+O`, then `Enter`, then exit with `Ctrl+X`. Then run:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> ⚠️ The seed creates the admin login: `admin@gsmplatform.test` / `AdminPass1`.
> Log in and **change the password immediately** after launch. The seed also loads
> demo services/categories/FAQs — edit or delete them in the admin panel.

---

## Step 5 — Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Step 6 — Queue worker (background email/telegram)

Create a systemd service so emails/Telegram run in the background and restart automatically:

```bash
sudo nano /etc/systemd/system/gsm-queue.service
```

Paste:

```ini
[Unit]
Description=GSM Platform queue worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/gsm-platform
ExecStart=/usr/bin/php /var/www/gsm-platform/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Save (`Ctrl+O`, `Enter`, `Ctrl+X`), then enable and start it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now gsm-queue.service
sudo systemctl status gsm-queue.service   # should say: active (running)
```

---

## Step 7 — Scheduler (auto-expire unpaid orders)

```bash
crontab -e
```

Add this line (pick `nano` if it asks which editor):

```
* * * * * cd /var/www/gsm-platform && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 8 — Nginx + HTTPS

```bash
sudo cp deploy/nginx-gsm-platform.conf /etc/nginx/sites-available/gsm-platform
sudo nano /etc/nginx/sites-available/gsm-platform
```

Edit the `server_name` lines (both server blocks) to your domain. The SSL certificate
lines near the top reference `/etc/letsencrypt/...` — those will be filled in by Certbot
below, so leave them for now. Save and exit.

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/gsm-platform /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Get a free HTTPS certificate:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
sudo certbot renew --dry-run
```

Certbot rewrites the SSL lines in the config automatically. If it asks you to choose
between redirect and no redirect, choose **redirect** (2). Reload Nginx again afterward:

```bash
sudo systemctl reload nginx
```

---

## Step 9 — Backups (set up now, not later)

```bash
mkdir -p ~/backups
crontab -e
```

Add (replace `<password>`):

```
0 3 * * 0 mysqldump -u gsm -p'<password>' gsm_service_platform | gzip > ~/backups/db-$(date +\%F).sql.gz
0 4 * * 0 tar -czf ~/backups/storage-$(date +\%F).tar.gz -C /var/www/gsm-platform storage/
```

Copy those files off the server weekly (e.g. to a cloud drive). See `docs/BACKUP-RESTORE.md` for how to restore.

---

## Step 10 — Verify it works

```bash
curl -I https://your-domain.com/up        # expect: HTTP/1.1 200
```

Then in a browser:
1. Open `https://your-domain.com` — homepage loads.
2. Log in to `/admin` with the seeded credentials.
3. Go to **System health** — DB/storage/queue/cache/scheduler should be CONNECTED.
   Mail/Telegram may show a warning until you configure real SMTP/Telegram credentials.
4. Do a live test: register a customer, place an order, verify it appears in admin,
   change its status, and confirm the customer sees the update.

---

## Post-launch security reminders

- Change the admin password (`admin@gsmplatform.test` / `AdminPass1`) immediately.
- Enable staff 2FA in Admin → Staff security.
- Keep `APP_DEBUG=false` (already set in `.env.production.example`).
- Never commit or upload `.env` (it holds secrets).
- Enable the queue worker status: `sudo systemctl status gsm-queue.service`.

## Updating the app later

```bash
cd /var/www/gsm-platform
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force     # back up the DB first
sudo systemctl restart gsm-queue.service
php artisan optimize:clear && php artisan optimize
```
