# 🚀 Deployment Guide - Attendance Management System

This guide will help you deploy your Laravel Attendance Management System to a live server so everyone can access it on phones and laptops.

## 📋 Table of Contents

1. [Choose Your Hosting Platform](#choose-your-hosting-platform)
2. [Option A: Deploy to Shared Hosting (cPanel)](#option-a-deploy-to-shared-hosting-cpanel)
3. [Option B: Deploy to VPS (DigitalOcean/Linode/Vultr)](#option-b-deploy-to-vps-digitaloceanlinodevultr)
4. [Option C: Deploy to Cloud Platforms](#option-c-deploy-to-cloud-platforms)
5. [Post-Deployment Checklist](#post-deployment-checklist)

---

## Choose Your Hosting Platform

### Recommended Options:

1. **Shared Hosting (cPanel)** - ⭐ Easiest for beginners
   - Cost: $3-10/month
   - Providers: Namecheap, Hostinger, Bluehost
   - Best for: Quick deployment, minimal technical knowledge

2. **VPS (Virtual Private Server)** - ⭐ Most flexible
   - Cost: $5-20/month
   - Providers: DigitalOcean, Linode, Vultr
   - Best for: Full control, scalable

3. **Cloud Platforms**
   - **Railway** or **Render** - Easiest cloud deployment
   - **Heroku** - Simple but can be expensive
   - **AWS/GCP** - Enterprise-level (more complex)

---

## Option A: Deploy to Shared Hosting (cPanel)

### Step 1: Purchase Hosting
1. Sign up for a hosting provider (e.g., Namecheap, Hostinger)
2. Choose a plan with:
   - PHP 8.1+ support
   - MySQL database
   - At least 1GB storage
   - cPanel access

### Step 2: Prepare Your Project

On your local computer:

```bash
# 1. Build production assets
npm run build

# 2. Install composer dependencies (without dev packages)
composer install --optimize-autoloader --no-dev

# 3. Clear and cache config
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Upload Files via FTP/cPanel File Manager

**Files to Upload:**
- All files EXCEPT:
  - `node_modules/` (don't upload)
  - `.env` (create new on server)
  - `.git/` (don't upload)
  - `tests/` (optional)
  - `storage/logs/*.log` (don't upload)

**Upload to:** `public_html/` or `public_html/attendance/` (if subdirectory)

### Step 4: Database Setup

1. **Create Database in cPanel:**
   - Go to cPanel → MySQL Databases
   - Create a new database (e.g., `username_attendance`)
   - Create a new MySQL user
   - Add user to database with ALL privileges

2. **Create `.env` file on server:**
   - Use cPanel File Manager
   - Navigate to your project root
   - Create `.env` file with:

```env
APP_NAME="Attendance Tracker"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

TESSERACT_PATH=""

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

3. **Generate App Key:**
   - In cPanel Terminal (or via SSH):
   ```bash
   cd public_html
   php artisan key:generate
   ```

### Step 5: Fix File Permissions

Via cPanel File Manager or SSH:

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
```

### Step 6: Run Migrations

```bash
php artisan migrate --force
```

### Step 7: Link Storage

```bash
php artisan storage:link
```

### Step 8: Update `.htaccess` for Public Folder

Ensure `public/.htaccess` exists and points to `public/` folder correctly.

### Step 9: Clear Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Option B: Deploy to VPS (DigitalOcean/Linode/Vultr)

### Step 1: Create a Droplet/VPS

1. Sign up at DigitalOcean/Linode/Vultr
2. Create a new droplet:
   - **OS:** Ubuntu 22.04 LTS
   - **Plan:** $6-12/month (1GB RAM minimum)
   - **Region:** Choose closest to your users
   - **Add SSH Key** (recommended) or use password

### Step 2: Initial Server Setup

Connect via SSH:

```bash
ssh root@your_server_ip
```

Update system:

```bash
apt update && apt upgrade -y
```

### Step 3: Install LAMP Stack

**Install Nginx:**
```bash
apt install nginx -y
systemctl start nginx
systemctl enable nginx
```

**Install MySQL:**
```bash
apt install mysql-server -y
mysql_secure_installation
```

Create database:
```sql
mysql -u root -p
CREATE DATABASE attendance_tracker;
CREATE USER 'attendance_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON attendance_tracker.* TO 'attendance_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Install PHP 8.2:**
```bash
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y
```

**Install Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

**Install Node.js (for building assets):**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs
```

### Step 4: Deploy Your Application

**Option A: Git Clone (Recommended)**

```bash
cd /var/www
git clone https://github.com/yourusername/attendance-tracker.git
cd attendance-tracker
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

**Option B: Upload via SCP**

On your local machine:
```bash
scp -r /path/to/attendance-tracker root@your_server_ip:/var/www/
```

Then on server:
```bash
cd /var/www/attendance-tracker
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### Step 5: Configure Environment

```bash
cp .env.example .env
nano .env
```

Update with your settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_DATABASE=attendance_tracker
DB_USERNAME=attendance_user
DB_PASSWORD=your_password
```

Generate key:
```bash
php artisan key:generate
```

### Step 6: Set Permissions

```bash
chown -R www-data:www-data /var/www/attendance-tracker
chmod -R 755 /var/www/attendance-tracker
chmod -R 775 /var/www/attendance-tracker/storage
chmod -R 775 /var/www/attendance-tracker/bootstrap/cache
```

### Step 7: Run Migrations

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 8: Configure Nginx

Create Nginx config:

```bash
nano /etc/nginx/sites-available/attendance-tracker
```

Add:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/attendance-tracker/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
ln -s /etc/nginx/sites-available/attendance-tracker /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### Step 9: Setup SSL with Let's Encrypt

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Step 10: Setup Firewall

```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

---

## Option C: Deploy to Cloud Platforms

### Railway.app (Easiest)

1. Sign up at [railway.app](https://railway.app)
2. Click "New Project" → "Deploy from GitHub repo"
3. Connect your GitHub repository
4. Add environment variables in Railway dashboard
5. Add MySQL database service
6. Deploy!

Railway automatically:
- Detects Laravel
- Runs migrations
- Builds assets
- Provides HTTPS

### Render.com

1. Sign up at [render.com](https://render.com)
2. Create new "Web Service"
3. Connect GitHub repo
4. Settings:
   - **Build Command:** `composer install --optimize-autoloader --no-dev && npm install && npm run build`
   - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`
   - **Environment:** PHP
5. Add PostgreSQL database (or MySQL)
6. Add environment variables
7. Deploy!

---

## Post-Deployment Checklist

### ✅ Essential Steps

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
- [ ] Set correct `APP_URL` (https://yourdomain.com)
- [ ] Generated `APP_KEY` with `php artisan key:generate`
- [ ] Database created and configured
- [ ] Migrations run successfully
- [ ] Storage link created: `php artisan storage:link`
- [ ] File permissions set correctly (storage and bootstrap/cache writable)
- [ ] Frontend assets built: `npm run build`
- [ ] Caches cleared and optimized
- [ ] SSL certificate installed (for HTTPS)
- [ ] Test registration/login
- [ ] Test file uploads (timetable images)
- [ ] Test PDF generation

### 🔒 Security Checklist

- [ ] `.env` file is NOT publicly accessible
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] Firewall configured (if VPS)
- [ ] Regular backups setup
- [ ] SSL/HTTPS enabled

### 🚀 Performance Optimization

```bash
# On server, run these commands:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 📱 Mobile Testing

Test on:
- [ ] iPhone (Safari)
- [ ] Android (Chrome)
- [ ] Desktop (Chrome, Firefox, Safari)
- [ ] Tablet

---

## Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check file permissions
ls -la storage bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Connection Error

- Verify database credentials in `.env`
- Check if MySQL is running: `systemctl status mysql`
- Test connection: `mysql -u username -p database_name`

### Assets Not Loading

```bash
# Rebuild assets
npm run build

# Clear browser cache
# Or add cache busting to URLs
```

### Permission Denied Errors

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### OCR Not Working

- Install Tesseract on server: `apt install tesseract-ocr`
- Update `TESSERACT_PATH` in `.env` if needed
- Check server logs for OCR errors

---

## Domain Setup

### Pointing Domain to Server

1. **Get Server IP** (for VPS) or **Nameservers** (for shared hosting)

2. **Update DNS Records:**
   - Go to your domain registrar (GoDaddy, Namecheap, etc.)
   - Add A Record:
     - **Type:** A
     - **Host:** @
     - **Points to:** Your server IP
     - **TTL:** 3600
   - Add CNAME for www:
     - **Type:** CNAME
     - **Host:** www
     - **Points to:** yourdomain.com

3. **Wait for DNS Propagation** (up to 48 hours, usually 1-2 hours)

---

## Backup Strategy

### Automated Backups (VPS)

Create backup script:

```bash
nano /root/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/root/backups"
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u username -p'password' attendance_tracker > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/attendance-tracker

# Delete backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete
```

Make executable:
```bash
chmod +x /root/backup.sh
```

Add to cron (daily at 2 AM):
```bash
crontab -e
# Add: 0 2 * * * /root/backup.sh
```

---

## Need Help?

- Check Laravel logs: `storage/logs/laravel.log`
- Check web server logs (Nginx: `/var/log/nginx/error.log`)
- Test locally first before deploying
- Start with shared hosting if you're new to deployment

---

## Quick Deploy Commands Reference

```bash
# Production build
npm run build
composer install --optimize-autoloader --no-dev

# Deploy
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

**🎉 Congratulations! Your app is now live and accessible worldwide!**

