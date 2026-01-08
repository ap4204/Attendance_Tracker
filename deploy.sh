#!/bin/bash

# Laravel Deployment Script
# Run this script on your server after uploading files

echo "🚀 Starting Laravel Deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    echo "Please create .env file first (copy from .env.example)"
    exit 1
fi

echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
composer install --optimize-autoloader --no-dev

echo -e "${YELLOW}📦 Installing NPM dependencies...${NC}"
npm install

echo -e "${YELLOW}🏗️  Building frontend assets...${NC}"
npm run build

echo -e "${YELLOW}🔑 Generating application key...${NC}"
php artisan key:generate --force

echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
php artisan migrate --force

echo -e "${YELLOW}🔗 Creating storage link...${NC}"
php artisan storage:link

echo -e "${YELLOW}🗑️  Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo -e "${YELLOW}⚡ Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${YELLOW}🔒 Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}🌐 Your application should now be live!${NC}"

