# Deploying the GSM Platform — A Beginner's Step-by-Step Guide

This guide assumes you are new to servers and never deployed a PHP app before. You will set up an Ubuntu server, install everything the app needs, copy your files up, and make it live with HTTPS. Follow the steps in order. Where a step is optional but recommended, it is marked `(recommended)`.

The app needs: PHP 8.2+, MySQL, Composer (PHP packages), Node.js (frontend build), Nginx (web server), and a couple of background jobs.

---

## Part A — Buy / prepare a server

1. Rent a small VPS (Ubuntu 22.04 or 24.04, 1-2 GB RAM is enough to start) from any provider (DigitalOcean, Linode, Hetzner, OVH).
2. Log into the provider panel and:
   - Create a "root" password or SSH key.
   - Note the **server IP address** (example: `203.0.113.10`).
3. Optionally, point a domain at the server by adding an `A` record: name `@` (or `www`) → your server IP. HTTPS is much easier with a real domain.

---

## Part B — First-time login

Open a terminal (macOS/Linux: Terminal; Windows: PowerShell) and connect:

```bash
ssh root@203.0.113.10
```

You will be asked for the password (paste it; the screen will not show the typing). Update the system:

```bash
apt update && apt upgrade -y
```

---

## Part C — Create a normal user (recommended)

It is safer to not run the app as `root`.

```bash
adduser deploy
usermod -aG sudo deploy
# log out and back in as deploy from now on:
ssh deploy@203.0.113.10
```

From here on use the `deploy` user.

---

## Part D — Install the software stack

Run these one after another (paste each block; some may take minutes):

```bash
sudo apt install -y software-properties-common curl git nginx mysql-server
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl
```

Check PHP:

```bash
php -v
```

Now install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

Install Node.js (for building the frontend):

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
```

---

## Part E — Secure the database

```bash
sudo mysql
```

Inside the `mysql>` prompt run (replace the two passwords):

```
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'CHANGE_ME_ROOT';
CREATE DATABASE gsm_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gsm'@'localhost' IDENTIFIED BY 'CHANGE_ME_DB';
GRANT ALL PRIVILEGES ON gsm_platform.* TO 'gsm'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Part F — Put the app on the server

On **your own computer** (where the project lives), package the app **without** unneeded local files:

```bash
# from inside your gsm-platform folder
tar --exclude=node_modules --exclude=vendor --exclude=.env --exclude=storage/app --exclude=storage/logs --exclude=storage/framework/cache --exclude=storage/framework/sessions --exclude=storage/framework/views --exclude=.git -czf gsm.tar.gz .
```

Upload it (on your computer):

```bash
scp gsm.tar.gz deploy@203.0.113.10:/home/deploy/
```

On the server, unpack and place it:

```bash
sudo mkdir -p /var/www/gsm-platform
sudo chown deploy:deploy /var/www/gsm-platform
cd /var/www/gsm-platform
tar -xzf /home/deploy/gsm.tar.gz
```

---

## Part G — Install dependencies & build assets

```bash
cd /var/www/gsm-platform
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

---

## Part H — Environment configuration

Create the environment file:

```bash
cp .env.example .env
```

Edit it (`sudo nano .env` or `nano .env`) and set these EXACT values appropriate for production:

```ini
APP_NAME="GSM Platform"
APP_ENV=production
APP_KEY=            # leave blank for now, we generate it next
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gsm_platform
DB_USERNAME=gsm
DB_PASSWORD=CHANGE_ME_DB

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com            # your provider's SMTP server
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=APP_PASSWORD          # Gmail app password, not your gmail password
MAIL_FROM_ADDRESS=you@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Generate the application key (this is what encrypts passwords and 2FA secrets):

```bash
cd /var/www/gsm-platform
php artisan key:generate
```

Then run the empty `php artisan config:clear` once (safety check) and check the app boots:

```bash
php artisan config:clear
php artisan about
```

---

## Part I — Create the database tables & admin

```bash
php artisan migrate --seed --force
```

This creates all tables and inserts: roles, permissions, payment methods, a sample category/service, and ONE admin account:

- Email: **admin@gsmplatform.test**
- Password: **AdminPass1**

**Immediately change the admin password** after the first login, and set up 2FA from `Profile → Security → Two-factor authentication` (recommended and required for safety).

> The seeder contains demo data. For a production clean start, you can delete the demo service/category later from the admin panel, or re-run the seeder and remove what you don't want.

---

## Part J — Storage permissions

Uploaded files and caches need write permission:

```bash
cd /var/www/gsm-platform
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

If you moved an existing app with uploaded order files, copy them back into `storage/app/private`.

---

## Part K — Nginx + HTTPS

**1. Create the Nginx site.** Create the file `/etc/nginx/sites-available/gsm-platform`:

```bash
sudo nano /etc/nginx/sites-available/gsm-platform
```

Paste (replace `your-domain.com` with your real domain or IP):

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/gsm-platform/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt { access_log off; log_not_found off; }

    # Never execute PHP inside /storage or /public/build
    location ~* ^/(storage|vendor|bootstrap|cache)/.*\.php$ { deny all; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* { deny all; }

    client_max_body_size 12m;
}
```

**2. Enable the site and reload Nginx:**

```bash
sudo ln -s /etc/nginx/sites-available/gsm-platform /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Open `http://your-domain.com` in a browser — you should see the site.

**3. Add HTTPS with Certbot (recommended — do not run a real shop without it):**

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

Certbot edits the Nginx config for you and sets up automatic renewal. Test the renewal:

```bash
sudo certbot renew --dry-run
```

---

## Part L — Background jobs (queue + scheduler)

The app sends emails/Telegram in the background. Set up two workers:

**1. Queue worker** — runs forever; use `systemd` so it restarts automatically. Create `/etc/systemd/system/gsm-queue.service`:

```ini
[Unit]
Description=GSM Platform queue worker
After=network.target

[Service]
User=deploy
Group=www-data
WorkingDirectory=/var/www/gsm-platform
ExecStart=/usr/bin/php /var/www/gsm-platform/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Enable and start it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now gsm-queue.service
sudo systemctl status gsm-queue.service        # should say active (running)
```

**2. Scheduler** — runs pending jobs every minute. Add a cron line:

```bash
crontab -e
```

Add this line (paste):

```
* * * * * cd /var/www/gsm-platform && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler invokes `orders:expire-unpaid` (auto-cancel unpaid orders) on its schedule.

---

## Part M — Final checks

```bash
cd /var/www/gsm-platform
php artisan migrate --force
php artisan route:list --except-vendor         # you should see lots of routes
php artisan config:clear
php artisan optimize                            # cache routes/config for speed (safe AFTER deploy)
curl -I https://your-domain.com/up              # health endpoint: expect 200
```

Then do the full customer → admin → customer walkthrough on the live site:

1. Register a customer, verify email, place an order, view tracking.
2. Log in as admin, see the order, verify payment, change status to PROCESSING, add a result, mark COMPLETED.
3. Confirm the customer sees the new status and result.

---

## Part N — Backups (do this from day one)

Set a weekly database + files backup via cron:

```bash
crontab -e
```

Add:

```
0 3 * * 0 mysqldump -u gsm -p'CHANGE_ME_DB' gsm_platform | gzip > /home/deploy/backups/gsm-$(date +\%F).sql.gz
0 4 * * 0 tar -czf /home/deploy/backups/storage-$(date +\%F).tar.gz -C /var/www/gsm-platform storage/
```

Create the folder first: `mkdir -p /home/deploy/backups`. Copy backups off the server (to a cloud drive) at least weekly. See `docs/BACKUP-RESTORE.md` for restores.

---

## Part O — Updating the app later

```bash
cd /var/www/gsm-platform
# 1. upload the new files (as in Part F)
# 2. install any new PHP/JS packages
composer install --no-dev --optimize-autoloader
npm install && npm run build
# 3. run new database migrations (BACK UP FIRST)
php artisan migrate --force
# 4. restart the queue so it uses the new code
sudo systemctl restart gsm-queue.service
php artisan optimize:clear && php artisan optimize
```

---

## Security reminder

- `APP_DEBUG` must stay `false` in production (it never shows error details to the public).
- Change DB passwords and the admin password; enable staff 2FA.
- Keep the MySQL root and app user passwords strong and stored in a password manager.
- `.env` is never committed or uploaded (it contains secrets). If you ever see `.env` in the repo, remove it immediately.
- See `docs/DEPLOYMENT.md` (specific config) and `docs/SECURITY-AUDIT.md` for the full checklist.