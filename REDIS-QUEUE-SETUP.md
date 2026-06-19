# Redis + Queue Worker Setup Guide

Run these commands on your server after deploying the latest code.

---

## 1. Install Redis on the server (if not already installed)

```bash
sudo apt update
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Verify Redis is running
redis-cli ping
# Should return: PONG
```

---

## 2. Install Supervisor (keeps the queue worker alive)

```bash
sudo apt install supervisor -y
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 3. Run pending migrations (creates failed_jobs table if not already created)

```bash
cd /var/www/html
php artisan migrate --force
```

---

## 4. Copy the Supervisor config

```bash
# Replace /var/www/html with your actual project path
sudo cp /var/www/html/supervisor-worker.conf /etc/supervisor/conf.d/iccr-alumni-worker.conf

# Edit the file and update the path if your project is not at /var/www/html
sudo nano /etc/supervisor/conf.d/iccr-alumni-worker.conf
```

---

## 5. Reload Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start iccr-alumni-worker:*

# Check status
sudo supervisorctl status
```

You should see:
```
iccr-alumni-worker:iccr-alumni-worker_00   RUNNING   pid 12345, uptime 0:00:05
iccr-alumni-worker:iccr-alumni-worker_01   RUNNING   pid 12346, uptime 0:00:05
```

---

## 6. Clear config cache (IMPORTANT — do this on every deploy)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 7. Verify queued jobs are working

```bash
# Watch the queue worker log in real time
tail -f /var/www/html/storage/logs/worker.log

# Or check the Laravel log
tail -f /var/www/html/storage/logs/laravel.log
```

When a contact form is submitted or an OTP is sent, you should see log entries confirming the job was picked up.

---

## 8. On every code deploy — restart the worker

```bash
sudo supervisorctl restart iccr-alumni-worker:*
```

Or add this to your deployment script:
```bash
php artisan queue:restart
```

---

## Redis DB layout (isolated namespaces)

| Redis DB | Purpose       |
|----------|---------------|
| DB 0     | General (default) |
| DB 1     | Cache         |
| DB 2     | Sessions      |
| DB 3     | Queue jobs    |

---

## Troubleshooting

**Worker not picking up jobs:**
```bash
sudo supervisorctl status
sudo supervisorctl tail iccr-alumni-worker:iccr-alumni-worker_00 stderr
```

**Redis connection refused:**
```bash
redis-cli ping
sudo systemctl restart redis-server
```

**Failed jobs:**
```bash
php artisan queue:failed          # list failed jobs
php artisan queue:retry all       # retry all failed jobs
php artisan queue:flush           # clear all failed jobs
```
