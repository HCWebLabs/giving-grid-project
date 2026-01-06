# The Giving Grid — Deployment Guide

A complete guide to deploying The Giving Grid on a production server.

---

## Table of Contents

1. [Requirements](#requirements)
2. [Server Setup Options](#server-setup-options)
3. [Shared Hosting Deployment](#shared-hosting-deployment)
4. [VPS Deployment](#vps-deployment)
5. [Database Setup](#database-setup)
6. [Environment Configuration](#environment-configuration)
7. [File Permissions](#file-permissions)
8. [SSL/HTTPS Setup](#sslhttps-setup)
9. [Post-Deployment Checklist](#post-deployment-checklist)
10. [Maintenance & Backups](#maintenance--backups)
11. [Troubleshooting](#troubleshooting)

---

## Requirements

### Minimum Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.1+ | 8.2+ |
| MySQL | 5.7+ | 8.0+ |
| Memory | 512MB | 1GB+ |
| Storage | 1GB | 5GB+ |

### Required PHP Extensions

```
pdo_mysql
mbstring
openssl
json
session
```

Check your PHP configuration:
```bash
php -m | grep -E "pdo_mysql|mbstring|openssl|json|session"
```

---

## Server Setup Options

### Option A: Shared Hosting (Easiest)
Best for: Getting started, low traffic, budget-friendly

**Recommended providers:**
- SiteGround ($3-6/mo)
- A2 Hosting ($3-5/mo)
- Hostinger ($2-4/mo)
- DreamHost ($3-5/mo)

### Option B: VPS (More Control)
Best for: Growing traffic, custom configuration

**Recommended providers:**
- DigitalOcean ($6/mo Droplet)
- Linode ($5/mo Nanode)
- Vultr ($5/mo)
- Hetzner ($4/mo - great value)

### Option C: Platform-as-a-Service
Best for: Zero server management

**Options:**
- Railway.app (free tier available)
- Render.com (free tier available)
- Laravel Forge + DigitalOcean

---

## Shared Hosting Deployment

### Step 1: Prepare Files

1. Download the latest `giving-grid-batch6.zip`
2. Extract locally
3. Create `.env` file from `.env.example`

### Step 2: Upload Files

Using cPanel File Manager or FTP:

```
your-hosting-account/
├── public_html/          ← Upload contents of /public here
│   ├── index.php
│   ├── .htaccess
│   └── assets/
└── giving-grid/          ← Upload everything else here (above public_html)
    ├── app/
    ├── config/
    ├── database/
    ├── storage/
    └── .env
```

### Step 3: Update Paths

Edit `public_html/index.php`:

```php
<?php
// Change this line:
define('BASE_PATH', dirname(__DIR__));

// To point to your app folder:
define('BASE_PATH', dirname(__DIR__) . '/giving-grid');
```

### Step 4: Create Database

1. In cPanel → MySQL Databases
2. Create new database: `yourusername_givinggrid`
3. Create new user with strong password
4. Add user to database with ALL PRIVILEGES

### Step 5: Import Schema

1. In cPanel → phpMyAdmin
2. Select your database
3. Import → Choose `database/schema.sql`
4. Then import `database/seeds/causes.sql`
5. Optionally import `database/seeds/demo.sql` for test data

### Step 6: Configure Environment

Edit `.env` in your `giving-grid` folder:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_NAME=yourusername_givinggrid
DB_USER=yourusername_dbuser
DB_PASS=your_secure_password
```

---

## VPS Deployment

### Step 1: Initial Server Setup (Ubuntu 22.04/24.04)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl mysql-server unzip certbot python3-certbot-nginx

# Secure MySQL
sudo mysql_secure_installation
```

### Step 2: Create Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE givinggrid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'givinggrid'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON givinggrid.* TO 'givinggrid'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Upload Application

```bash
# Create web directory
sudo mkdir -p /var/www/givinggrid
cd /var/www/givinggrid

# Upload and extract (or use git)
sudo unzip giving-grid-batch6.zip
sudo mv giving-grid/* .
sudo rm -rf giving-grid

# Set ownership
sudo chown -R www-data:www-data /var/www/givinggrid
```

### Step 4: Configure Nginx

Create `/etc/nginx/sites-available/givinggrid`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/givinggrid/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ^/(config|app|database|storage)/ {
        deny all;
    }

    # Cache static assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/givinggrid /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 5: Import Database

```bash
cd /var/www/givinggrid
mysql -u givinggrid -p givinggrid < database/schema.sql
mysql -u givinggrid -p givinggrid < database/seeds/causes.sql
```

### Step 6: Configure Environment

```bash
sudo cp .env.example .env
sudo nano .env
```

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_NAME=givinggrid
DB_USER=givinggrid
DB_PASS=your_secure_password_here
```

---

## Database Setup

### Schema Overview

The Giving Grid uses 8 tables:

| Table | Purpose |
|-------|---------|
| `users` | User accounts |
| `organizations` | Nonprofit profiles |
| `listings` | Needs, offers, volunteer opps |
| `causes` | Category tags |
| `listing_causes` | Listing-cause relationships |
| `responses` | User responses to listings |
| `response_messages` | Thread messages |
| `reports` | User reports for moderation |

### Create Admin User

After deployment, create your admin account:

```sql
INSERT INTO users (email, password_hash, display_name, role, is_active, created_at, updated_at)
VALUES (
    'admin@yourdomain.com',
    '$2y$10$YOUR_BCRYPT_HASH_HERE',
    'Admin',
    'admin',
    1,
    NOW(),
    NOW()
);
```

Generate password hash with PHP:
```bash
php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
```

Or register normally and update the role:
```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@yourdomain.com';
```

---

## Environment Configuration

### Required Settings

```env
# Application
APP_ENV=production      # Use 'development' for debugging
APP_DEBUG=false         # Set true only for debugging
APP_URL=https://yourdomain.com
APP_NAME="The Giving Grid"

# Database
DB_HOST=localhost
DB_NAME=givinggrid
DB_USER=givinggrid
DB_PASS=secure_password_here

# Session (optional)
SESSION_LIFETIME=120    # Minutes
```

### Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database password (16+ chars, mixed)
- [ ] HTTPS enabled (see SSL section)
- [ ] `.env` file not accessible via web

---

## File Permissions

### Linux/VPS

```bash
cd /var/www/givinggrid

# Directories: 755 (rwxr-xr-x)
find . -type d -exec chmod 755 {} \;

# Files: 644 (rw-r--r--)
find . -type f -exec chmod 644 {} \;

# Storage needs write access
chmod -R 775 storage/
chown -R www-data:www-data storage/

# Protect sensitive files
chmod 600 .env
```

### Shared Hosting

Most shared hosts handle this automatically, but ensure:
- `storage/` folder is writable (755 or 775)
- `.env` is not publicly accessible

---

## SSL/HTTPS Setup

### Using Let's Encrypt (VPS)

```bash
# Install certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically
# Test with:
sudo certbot renew --dry-run
```

### Shared Hosting

Most providers offer free SSL:
1. Go to cPanel → SSL/TLS or Let's Encrypt
2. Select your domain
3. Install certificate
4. Enable "Force HTTPS" redirect

### Update .htaccess for HTTPS

Add to `public/.htaccess`:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Post-Deployment Checklist

### Immediate

- [ ] Site loads at your domain
- [ ] HTTPS working (green padlock)
- [ ] Registration works
- [ ] Login works
- [ ] Can create a listing
- [ ] Can respond to a listing
- [ ] Admin panel accessible at `/admin`

### Security

- [ ] `.env` returns 403/404 when accessed directly
- [ ] `/config/` returns 403/404
- [ ] `/app/` returns 403/404
- [ ] `APP_DEBUG=false` confirmed
- [ ] Admin account created with strong password

### Functionality

- [ ] Browse page loads listings
- [ ] Filters work (type, county, category)
- [ ] Organization profiles display
- [ ] Flash messages appear
- [ ] Mobile responsive

### Performance

- [ ] Pages load in < 3 seconds
- [ ] CSS/JS loading correctly
- [ ] Images displaying

---

## Maintenance & Backups

### Database Backup Script

Create `/var/www/givinggrid/backup.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/givinggrid"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u givinggrid -p'your_password' givinggrid > $BACKUP_DIR/db_$DATE.sql

# Compress
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

```bash
chmod +x backup.sh
```

### Automated Daily Backups (Cron)

```bash
crontab -e
```

Add:
```
0 3 * * * /var/www/givinggrid/backup.sh >> /var/log/givinggrid-backup.log 2>&1
```

### Updates

When updating the application:

```bash
# Backup first!
./backup.sh

# Upload new files (preserve .env)
# If using git:
git pull origin main

# Clear any cached files
rm -rf storage/cache/*

# Run any new migrations if provided
mysql -u givinggrid -p givinggrid < database/migrations/new_migration.sql
```

---

## Troubleshooting

### 500 Internal Server Error

1. Check PHP error log:
   ```bash
   tail -f /var/log/php8.2-fpm.log
   # or for Apache:
   tail -f /var/log/apache2/error.log
   ```

2. Enable debug temporarily:
   ```env
   APP_DEBUG=true
   ```

3. Common causes:
   - Missing PHP extensions
   - Wrong file permissions
   - Database connection failed
   - Syntax error in .env

### Database Connection Failed

1. Verify credentials in `.env`
2. Test connection:
   ```bash
   mysql -u givinggrid -p -h localhost givinggrid
   ```
3. Check MySQL is running:
   ```bash
   sudo systemctl status mysql
   ```

### Blank Page

1. Check PHP error log
2. Verify `BASE_PATH` in `public/index.php`
3. Check file permissions on `storage/`

### 403 Forbidden

1. Check file ownership: `ls -la`
2. Nginx: Verify `root` path in config
3. Apache: Check `.htaccess` and `AllowOverride All`

### Session Issues

1. Verify `storage/` is writable
2. Check PHP session configuration:
   ```bash
   php -i | grep session
   ```

### CSS/JS Not Loading

1. Check browser console for 404s
2. Verify `assets/` folder uploaded to `public/`
3. Check `APP_URL` matches your domain

---

## Quick Reference

### File Structure (Production)

```
/var/www/givinggrid/
├── app/                 # Application code (not web accessible)
├── config/              # Configuration (not web accessible)
├── database/            # SQL files (not web accessible)
├── storage/             # Writable storage (not web accessible)
│   ├── cache/
│   ├── logs/
│   └── uploads/
├── public/              # Web root (point Nginx/Apache here)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── .env                 # Environment config (not web accessible)
└── README.md
```

### Key URLs

| URL | Purpose |
|-----|---------|
| `/` | Homepage |
| `/browse` | Browse listings |
| `/login` | Login page |
| `/register` | Registration |
| `/dashboard` | User dashboard |
| `/admin` | Admin panel |

### Default Demo Accounts

If you imported `demo.sql`:

| Email | Password | Role |
|-------|----------|------|
| admin@givinggrid.org | password123 | Admin |
| john.doe@email.com | password123 | Individual |
| sarah@knoxfoodbank.org | password123 | Org Member |

**Change these immediately in production!**

---

## Need Help?

1. Check this guide's troubleshooting section
2. Review PHP/Nginx error logs
3. Verify all environment variables
4. Test database connection independently

Good luck with your deployment! 🎉
