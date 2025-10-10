# qheap-track

Order and inventory management system built with Laravel + Vue.js + Inertia.

## For Development

```bash
# Install dependencies
composer install
bun install

# Setup environment
cp .env.example .env
# Edit .env with your database details
php artisan migrate

# Run development server
php artisan serve
```

## Building for Distribution

To create a portable desktop app for your dad:

```bash
./build.sh
```

This creates: `./dist/qheap-track-portable.zip`

**To add portable PHP (makes it truly standalone):**

1. Download PHP from: https://windows.php.net/downloads/releases/archives/php-8.3.13-Win32-vs16-x64.zip
2. Extract it to: `./dist/qheap-track/php/`
3. Re-zip the folder

OR use the helper script:
```bash
# After manual PHP download
./bundle-php.sh
```

## What Gets Built

- ✅ Laravel backend with all dependencies
- ✅ Compiled Vue.js/Inertia frontend  
- ✅ Your `.env` file (with database credentials)
- ✅ Windows launcher (`.bat`)
- ✅ Linux launcher (`.sh`)
- ✅ Portable PHP (if you add it)

## For End Users

**With portable PHP:**
1. Extract the zip
2. Double-click `qheap-track.bat` (Windows) or run `./qheap-track.sh` (Linux)
3. App opens in browser at http://localhost:8000

**Without portable PHP:**
They need PHP 8.3+ installed, then same steps above.

## Tech Stack

- Laravel 11 (PHP 8.3)
- Vue.js 3 + Inertia.js
- Bun (build tool)
- PostgreSQL / SQLite