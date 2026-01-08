# Setup Instructions

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL database

## Step-by-Step Setup

### 1. Install PHP Dependencies
```bash
composer install
```

### 2. Install Node Dependencies
```bash
npm install
```

### 3. Environment Configuration
```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

Edit `.env` file and configure your database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_tracker
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:
```sql
CREATE DATABASE attendance_tracker;
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 7. Create Storage Link (if needed)
```bash
php artisan storage:link
```

### 8. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## First Steps

1. **Register** a new account
2. **Add Subjects** - Go to Subjects page and add your subjects
3. **Create Timetable** - Upload an image or manually add timetable entries
4. **Mark Attendance** - Use the Dashboard to mark attendance daily
5. **View Reports** - Check your progress and download PDF reports

## Optional: Tesseract OCR Setup

If you want to use OCR for timetable image parsing:

### Windows
1. Download Tesseract from: https://github.com/UB-Mannheim/tesseract/wiki
2. Install it (default path: `C:\Program Files\Tesseract-OCR\tesseract.exe`)
3. Add to your `.env` file:
   ```env
   TESSERACT_PATH="C:\\Program Files\\Tesseract-OCR\\tesseract.exe"
   ```
   Note: Use double backslashes (`\\`) in the path

### Linux
```bash
sudo apt-get install tesseract-ocr
```
If installed in a non-standard location, add to `.env`:
```env
TESSERACT_PATH="/path/to/tesseract"
```

### macOS
```bash
brew install tesseract
```
If installed in a non-standard location, add to `.env`:
```env
TESSERACT_PATH="/path/to/tesseract"
```

Note: OCR is optional. You can always manually add timetable entries. If TESSERACT_PATH is not set, the application will try to auto-detect Tesseract from common installation paths.

## Troubleshooting

### Database Connection Error
- Verify database credentials in `.env`
- Ensure MySQL is running
- Check database exists

### Asset Compilation Error
- Run `npm install` again
- Clear cache: `npm cache clean --force`

### Permission Errors
- Ensure `storage` and `bootstrap/cache` directories are writable
- On Linux: `chmod -R 775 storage bootstrap/cache`

### PDF Generation Error
- Ensure `barryvdh/laravel-dompdf` is installed: `composer require barryvdh/laravel-dompdf`
- Check PHP extensions: `php -m | grep dom`

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Build assets: `npm run build`
6. Ensure proper file permissions on `storage` and `bootstrap/cache`

