# Installation Guide

This guide covers installation in three environments:

1. **Local development** (Linux / macOS / WSL with PHP built-in server).
2. **Apache shared hosting** (e.g. alwaysdata, OVH, Hostinger).
3. **Self-managed VPS** (Ubuntu + nginx + PHP-FPM + MariaDB).

---

## 1. Prerequisites

| Component | Version |
|---|---|
| PHP | ≥ 8.0 (8.2+ recommended) |
| PHP extensions | `pdo_mysql`, `mbstring`, `json`, `intl` |
| MySQL / MariaDB | MySQL 8.0+ or MariaDB 10.5+ |
| Database charset | utf8mb4 / utf8mb4_unicode_ci |
| Web server | Apache 2.4 with `mod_rewrite`, **or** nginx with PHP-FPM |
| Browser | any modern browser with JavaScript enabled |

Verify locally:

```bash
php -v
php -m | grep -E 'pdo_mysql|mbstring|intl|json'
mysql --version
```

---

## 2. Local development

```bash
# 1. Clone
git clone https://github.com/Touil-Elhadj/chlef-touilelhadj.git
cd chlef-touilelhadj

# 2. Environment
cp .env.example .env

# Edit .env:
#   APP_ENV=development
#   APP_DEBUG=1
#   DB_HOST=127.0.0.1
#   DB_NAME=chlef_biostat
#   DB_USER=root
#   DB_PASS=<your local mysql password>
#   SITE_URL=http://localhost:8000

# 3. Database
mysql -u root -p -e "CREATE DATABASE chlef_biostat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p chlef_biostat < database/schema.sql
mysql -u root -p chlef_biostat < database/seed.sql   # creates admin / change_me_immediately

# 4. Run
php -S localhost:8000
```

Open `http://localhost:8000/login.php`, log in as `admin` / `change_me_immediately`,
then **immediately change the password** from the admin panel.

> **Note**: the PHP built-in server is fine for development but never use it
> in production.

---

## 3. Apache shared hosting (e.g. alwaysdata)

These instructions assume you have:
- SSH or SFTP access,
- a MySQL database already provisioned by the host,
- a writable web root.

### 3.1 Upload

```bash
# from your local machine
rsync -avz --exclude-from=.gitignore \
  ./chlef-touilelhadj/ \
  user@host:/path/to/webroot/
```

Alternatively, upload the project archive via SFTP and unzip on the server.

### 3.2 Configure

On the server:

```bash
cd /path/to/webroot
cp .env.example .env
nano .env   # set the production values
chmod 0600 .env
```

Critical `.env` settings for production:

```
APP_ENV=production
APP_DEBUG=0
DB_HOST=<provided by host>
DB_NAME=<provided by host>
DB_USER=<provided by host>
DB_PASS=<provided by host — strong password>
SITE_URL=https://yourdomain.example
ADMIN_EMAIL=you@example.com
```

### 3.3 Permissions

```bash
# Code: read-only
find . -type f -not -path './logs/*' -not -path './.env' -exec chmod 0644 {} \;
find . -type d -exec chmod 0755 {} \;

# Logs: writable by the web user
chmod -R 0755 logs/

# .env: never world-readable
chmod 0600 .env
```

### 3.4 Database

If you have shell access:

```bash
mysql -h <DB_HOST> -u <DB_USER> -p <DB_NAME> < database/schema.sql
mysql -h <DB_HOST> -u <DB_USER> -p <DB_NAME> < database/seed.sql
```

If you only have phpMyAdmin, import `database/schema.sql` then `database/seed.sql`
through the *Import* tab.

### 3.5 Verify

1. Open `https://yourdomain/login.php` → the form should load with proper
   styling.
2. Log in as `admin` / `change_me_immediately` and **change the password**.
3. Open `https://yourdomain/admin.php` → the dashboard should load.
4. Submit one test response from `https://yourdomain/index.php` → the record
   should appear in `https://yourdomain/records.php`.
5. Open `https://yourdomain/tests/run_tests.php` (admin or guest auth) →
   20 / 20 should pass.

---

## 4. Self-managed VPS (Ubuntu 22.04 + nginx)

```bash
# Install LEMP
sudo apt update
sudo apt install -y nginx php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-intl mariadb-server git

# Database
sudo mysql_secure_installation
sudo mysql -e "CREATE DATABASE chlef_biostat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'biostat'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';"
sudo mysql -e "GRANT ALL ON chlef_biostat.* TO 'biostat'@'localhost'; FLUSH PRIVILEGES;"

# Code
sudo mkdir -p /var/www/chlef
sudo chown -R $USER:www-data /var/www/chlef
git clone https://github.com/Touil-Elhadj/chlef-touilelhadj.git /var/www/chlef
cd /var/www/chlef
cp .env.example .env
nano .env
chmod 0600 .env
chmod -R 0755 logs/

# Load schema
mysql -u biostat -p chlef_biostat < database/schema.sql
mysql -u biostat -p chlef_biostat < database/seed.sql
```

Minimal nginx site:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.example;

    root /var/www/chlef;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/yourdomain/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain/privkey.pem;

    # Block sensitive files
    location ~ /\.(env|git|htaccess) { deny all; }
    location ~ ^/(logs|database|tests|bin|docs)/ { deny all; }
    location ~ \.(log|sql|md|bib|cff|yml|yaml)$ { deny all; }
    location ~ ^/(config|computed_scores|BiostatPHP\.class|about_data\.inc)\.php$ {
        deny all;
    }

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}

server {
    listen 80;
    server_name yourdomain.example;
    return 301 https://$host$request_uri;
}
```

Reload nginx:

```bash
sudo nginx -t && sudo systemctl reload nginx
```

---

## 5. Migrations

Migrations live in `database/migrations/`. Apply them in alphabetical order:

```bash
for f in database/migrations/*.sql; do
  echo "Applying $f"
  mysql -u <DB_USER> -p <DB_NAME> < "$f"
done
```

For the current set:

| File | Purpose |
|---|---|
| `2026_04_01_add_screen_indicators.sql` | adds `screen_sum` and `screen_max` columns and recomputes them for existing rows (HBSC 2018 recommendation) |

---

## 6. Backup and restore

```bash
# Backup
mysqldump -u <DB_USER> -p --single-transaction --routines --triggers <DB_NAME> \
  | gzip > backup_$(date +%Y%m%d_%H%M).sql.gz

# Restore
gunzip -c backup_20260518_1430.sql.gz | mysql -u <DB_USER> -p <DB_NAME>
```

A CLI helper is provided as `bin/backup.php`.

---

## 7. Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| `Database connection error` after install | wrong DB credentials in `.env` | re-check `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` |
| Login page shows but submitting does nothing | sessions cannot be written | check `session.save_path` is writable by the web user |
| Arabic text shows as `????` | wrong charset | confirm DB uses `utf8mb4` and connection uses `charset=utf8mb4` (already set in `config.php`) |
| `.htaccess` rules ignored | `mod_rewrite` disabled | `sudo a2enmod rewrite && sudo systemctl restart apache2` |
| 500 error on every page | PHP version too old | upgrade to PHP ≥ 8.0 |
