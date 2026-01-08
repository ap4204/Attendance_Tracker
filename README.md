# Attendance Management System

A complete, production-ready Attendance Management System built with Laravel, Tailwind CSS, and MySQL. Features multi-user support, mobile-responsive design, and dark mode UI.

## Features

- **Authentication**: Laravel Breeze (Session-based) for Register/Login
- **Dynamic Timetable**: Upload timetable images with OCR support (manual override available)
- **Daily Dashboard**: View today's schedule and mark attendance with AJAX
- **Subjects Management**: Create and manage subjects with target percentages
- **Attendance Tracking**: Mark present/absent/cancelled for each class
- **Analytics & Reports**: View attendance progress with progress bars
- **PDF Export**: Download formal attendance reports for college proof
- **Mobile-First Design**: Fully responsive with Tailwind CSS
- **Dark Mode**: Clean dark theme throughout

## Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   - Update `.env` with your database credentials
   - Create a MySQL database named `attendance_tracker` (or your preferred name)

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Build Assets**
   ```bash
   npm run build
   # Or for development:
   npm run dev
   ```

6. **Start Development Server**
   ```bash
   php artisan serve
   ```

## Project Structure

### Database Schema

- **users**: Standard user fields
- **subjects**: id, user_id, name, target_percentage (default 75)
- **timetable_entries**: id, user_id, subject_id, day_of_week, start_time, end_time, specific_date (nullable)
- **attendances**: id, user_id, subject_id, timetable_entry_id, status, date, remarks

### Key Components

- **Controllers**: DashboardController, TimetableController, AttendanceController, SubjectController, ReportController
- **Services**: OcrService (for timetable image parsing)
- **Models**: User, Subject, TimetableEntry, Attendance (with relationships)
- **Views**: Blade templates with Tailwind CSS, mobile-first design

## Usage

1. **Register/Login**: Create an account or login
2. **Add Subjects**: Go to Subjects page and add your subjects
3. **Create Timetable**: 
   - Upload a timetable image (OCR will attempt to parse it)
   - Or manually add timetable entries
   - Confirm/edit parsed entries before saving
4. **Mark Attendance**: Use the Dashboard to mark attendance for today's classes
5. **View Reports**: Check your attendance progress and download PDF reports

## OCR Feature

The OCR service supports:
- Image upload for timetable parsing
- Regex-based pattern matching for time and subject extraction
- Manual override/editing before saving to database
- Tesseract OCR integration (optional, if installed)

## PDF Export

Generate formal attendance reports:
- Select month and optional subject filter
- Download PDF with complete attendance log
- Perfect for college submission

## Technologies

- **Backend**: Laravel 10
- **Frontend**: Blade Templates, Tailwind CSS, JavaScript
- **Database**: MySQL
- **PDF**: dompdf
- **Icons**: Lucide Icons (SVG)

## Deployment

### Quick Deploy (Recommended)

**Railway.app** or **Render.com** - Deploy in 5 minutes!

See [DEPLOY_QUICKSTART.md](DEPLOY_QUICKSTART.md) for fastest deployment option.

### Detailed Deployment Guide

For complete deployment instructions including:
- Shared Hosting (cPanel)
- VPS Setup (DigitalOcean, Linode)
- Cloud Platforms (Railway, Render, Heroku)
- Nginx Configuration
- SSL Setup
- Backup Strategies

See [DEPLOYMENT.md](DEPLOYMENT.md)

## License

MIT

