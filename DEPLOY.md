# Production Deployment Guide
## ICCR Alumni Portal — Apache + PHP + MySQL

---

## STEP 1 — Verify server requirements

SSH into your server and run these checks:

```bash
php -v          # Must be 8.1 or higher
mysql --version # Any recent MySQL/MariaDB
apache2 -v      # Any recent Apache
```

If PHP is below 8.1, upgrade it before continuing.

---

## STEP 2 — Install required PHP extensions

```bash
sudo apt update

sudo apt install -y \
  php8.1-mysql \
  php8.1-mbstring \
  php8.1-xml \
  php8.1-gd \
  php8.1-curl \
  php8.1-zip \
  php8.1-bcmath \
  php8.1-intl \
  php8.1-fileinfo \
  php8.1-tokenizer

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Check everything loaded:
```bash
php -m | grep -E "pdo_mysql|mbstring|xml|gd|curl|zip|bcmath|intl|fileinfo"
```
All 8 should appear.

Install Composer if not present:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## STEP 3 — Create the MySQL database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE alumni_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alumni_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON alumni_portal.* TO 'alumni_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## STEP 4 — Upload the application files

**From your Windows machine**, zip the project folder excluding heavy/sensitive items:

```
alumini-app/
  EXCLUDE: .env  vendor/  node_modules/  .git/
           storage/framework/cache/
           storage/framework/sessions/
           storage/framework/views/
           storage/logs/
```

Upload the zip to your server (scp, FileZilla, cPanel File Manager, etc.):

```bash
# Example with scp from Windows PowerShell:
scp alumni-portal.zip user@yourserver.com:/var/www/
```

On the server:
```bash
cd /var/www
sudo unzip alumni-portal.zip -d alumni-portal
sudo chown -R www-data:www-data alumni-portal
```

---

## STEP 5 — Install PHP dependencies on the server

```bash
cd /var/www/alumni-portal

# Install production dependencies only (no dev tools)
composer install --no-dev --optimize-autoloader
```

This will also install `phpoffice/phpspreadsheet` (added to composer.json)
which is required for XLSX import.

---

## STEP 6 — Create the production .env file

```bash
cd /var/www/alumni-portal
cp .env.production .env
nano .env          # or vim .env
```

Edit these values in .env:
```
APP_URL=https://yourdomain.com          ← your actual domain
DB_USERNAME=alumni_user
DB_PASSWORD=STRONG_PASSWORD_HERE        ← the password you set in Step 3
SUPER_ADMIN_PASSWORD=something-secure   ← change from default
```

The file already has:
- `SESSION_DRIVER=file`  ← no Redis needed
- `CACHE_DRIVER=file`    ← no Redis needed
- `APP_DEBUG=false`      ← safe for production

---

## STEP 7 — Set file permissions

```bash
cd /var/www/alumni-portal

# Laravel needs to write to these directories
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Make sure the log directory exists
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,views}
sudo chmod -R 775 storage
```

---

## STEP 8 — Run database migrations

```bash
cd /var/www/alumni-portal
php artisan migrate --force
```

The `--force` flag is required when `APP_ENV=production`.

Create the storage symlink (makes uploaded files publicly accessible):
```bash
php artisan storage:link
```

---

## STEP 9 — Configure Apache

Copy the VirtualHost config:
```bash
sudo cp /var/www/alumni-portal/iccr-alumni.conf /etc/apache2/sites-available/iccr-alumni.conf
```

Edit it to set your actual domain:
```bash
sudo nano /etc/apache2/sites-available/iccr-alumni.conf
# Change yourdomain.com to your actual domain
```

Enable it:
```bash
sudo a2ensite iccr-alumni.conf
sudo a2dissite 000-default.conf    # disable default site
sudo systemctl reload apache2
```

Verify Apache config is valid:
```bash
sudo apache2ctl configtest          # should say "Syntax OK"
```

---

## STEP 10 — Optimize for production

```bash
cd /var/www/alumni-portal

php artisan config:cache    # caches all config files
php artisan route:cache     # caches routes
php artisan view:cache      # pre-compiles Blade views
```

To clear caches later (e.g. after updating .env):
```bash
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

---

## STEP 11 — Set up the queue worker (background jobs)

Your app uses `QUEUE_CONNECTION=database`. The queue worker must keep running.

**Option A — Cron job (simplest, restarts every minute)**

```bash
crontab -e   # as www-data or root
```
Add this line:
```
* * * * * cd /var/www/alumni-portal && php artisan queue:work --once --queue=default >> /dev/null 2>&1
```

**Option B — Supervisor (recommended, keeps process alive)**

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/alumni-queue.conf
```

```ini
[program:alumni-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/alumni-portal/artisan queue:work --sleep=3 --tries=3 --queue=default
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/alumni-portal/storage/logs/queue.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start alumni-queue:*
```

---

## STEP 12 — Add HTTPS (free SSL with Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Follow the prompts. Certbot auto-renews via cron.

---

## STEP 13 — PHP configuration tweaks

For the bulk CSV upload (19k+ rows), set higher limits in php.ini:

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Find and update:
```ini
upload_max_filesize = 25M
post_max_size = 26M
max_execution_time = 300
memory_limit = 512M
max_input_time = 300
```

```bash
sudo systemctl restart apache2
```

---

## STEP 14 — Final checks

Visit your domain and verify:

- [ ] Home page loads
- [ ] Login works
- [ ] Admin panel accessible
- [ ] File uploads work (profile photo)
- [ ] CSV import works on `/alumni-data`
- [ ] Captcha appears on signup page
- [ ] Emails send (test via forgot password)
- [ ] No errors in `storage/logs/laravel.log`

Check logs if anything breaks:
```bash
tail -f /var/www/alumni-portal/storage/logs/laravel.log
tail -f /var/log/apache2/alumni-portal-error.log
```

---

## Deploying future updates

```bash
cd /var/www/alumni-portal

# 1. Upload new files (same exclude list as Step 4)
# 2. Run:
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload apache2
```

---

## Troubleshooting

**500 error on every page**
→ Check `storage/logs/laravel.log`. Usually a permission issue or missing .env key.

**"No application encryption key" error**
→ Your .env was lost. Re-copy `.env.production` to `.env` and edit DB credentials.

**Uploaded files not showing (profile photos)**
→ Run `php artisan storage:link` again.

**Captcha not displaying**
→ GD extension not loaded. Run `php -m | grep gd`. If missing: `sudo apt install php8.1-gd && sudo systemctl restart apache2`.

**CSV import times out**
→ Check `php.ini` values from Step 13. Also check Apache `TimeOut` directive in the VirtualHost config.
