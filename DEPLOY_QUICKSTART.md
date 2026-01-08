# 🚀 Quick Deployment Guide

## Fastest Way: Railway.app or Render.com

### Railway.app (Recommended - 5 minutes)

1. **Sign up:** https://railway.app (use GitHub)
2. **Create Project:** New Project → Deploy from GitHub
3. **Connect Repo:** Select your attendance-tracker repository
4. **Add Database:** Click "+ New" → Add MySQL
5. **Add Environment Variables:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-app-name.railway.app
   DB_HOST=(from MySQL service)
   DB_DATABASE=(from MySQL service)
   DB_USERNAME=(from MySQL service)
   DB_PASSWORD=(from MySQL service)
   ```
6. **Deploy:** Railway auto-detects Laravel and deploys!
7. **Custom Domain:** Add your domain in Railway settings

**Done!** Your app is live in ~5 minutes! 🎉

---

### Render.com (Alternative)

1. **Sign up:** https://render.com
2. **New Web Service:** Connect GitHub repo
3. **Settings:**
   - **Build Command:** `composer install --optimize-autoloader --no-dev && npm install && npm run build`
   - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`
   - **Environment:** PHP
4. **Add PostgreSQL Database:** New → PostgreSQL (free tier available)
5. **Environment Variables:** Same as Railway
6. **Deploy!**

---

## Easy Way: Shared Hosting (cPanel)

### Step-by-Step:

1. **Purchase hosting** (Hostinger, Namecheap, etc.) - ~$3-5/month
2. **Upload files** via FTP (FileZilla) or cPanel File Manager
3. **Upload to:** `public_html/` folder
4. **Create database** in cPanel → MySQL Databases
5. **Create `.env` file** in root (see DEPLOYMENT.md for template)
6. **Run via Terminal (cPanel):**
   ```bash
   composer install --no-dev
   npm install && npm run build
   php artisan key:generate
   php artisan migrate
   php artisan storage:link
   ```

---

## Need More Details?

See **DEPLOYMENT.md** for complete guide with:
- VPS setup (DigitalOcean, Linode)
- Nginx configuration
- SSL setup
- Backup strategies
- Troubleshooting

---

## Quick Checklist

Before going live:
- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] Database created and configured
- [ ] Migrations run
- [ ] Storage link created
- [ ] Assets built (`npm run build`)
- [ ] SSL certificate (for HTTPS)
- [ ] Test registration/login
- [ ] Test file uploads

---

**Questions?** Check DEPLOYMENT.md for detailed instructions!

