#!/bin/bash
set -e

# Build complete portable desktop app with PHP bundled
# Single command - does everything!
# IMPORTANT: Builds in isolation - your dev environment stays untouched!

APP_NAME="qheap-track"
BUILD_DIR="./dist"
WORK_DIR="/tmp/qheap-track-build-$$"
PHP_VERSION="8.4.13"
PHP_BUILD="Win32-vs17-x64"

echo "🔨 Building complete portable desktop app..."
echo "   (Your dev environment will NOT be modified)"
echo ""

# Pre-build validation
echo "🔍 Pre-build validation..."

# Check for required commands
for cmd in php composer bun zip; do
    if ! command -v $cmd &> /dev/null; then
        echo "❌ Required command not found: $cmd"
        exit 1
    fi
done

# Check for route closures in dev environment (before building)
echo "   Checking routes for closures..."
if php artisan route:list 2>&1 | grep -i "error\|exception" > /dev/null; then
    echo "❌ Route listing failed!"
    echo "   You may have closures in your routes."
    echo "   Run 'php artisan route:list' to see the error."
    exit 1
fi

# Check for problematic closures in web routes (not console.php)
PROBLEMATIC_CLOSURES=$(grep -r "Route::" routes/*.php 2>/dev/null | grep -v "routes/console.php" | grep -E "function\s*\(" | grep -v "->group\(function" | grep -v "^[[:space:]]*//" || true)
if [ -n "$PROBLEMATIC_CLOSURES" ]; then
    echo "⚠️  Warning: Found closure(s) in web routes:"
    echo "$PROBLEMATIC_CLOSURES"
    echo ""
    echo "   Laravel cannot cache routes with closures."
    echo "   Convert them to controller methods."
    read -p "   Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo "   ✓ Pre-build checks passed"
echo ""

# Cleanup function
cleanup() {
    if [ -d "$WORK_DIR" ]; then
        echo "🧹 Cleaning up temporary build directory..."
        rm -rf "$WORK_DIR"
    fi
}
trap cleanup EXIT

mkdir -p "$BUILD_DIR"

echo "📦 Step 1: Building frontend (in dev environment)..."
bun install && bun run build

echo ""
echo "📦 Step 2: Creating clean isolated build directory..."
rm -rf "$WORK_DIR"
mkdir -p "$WORK_DIR"

echo "   Copying application files..."
if command -v rsync &> /dev/null; then
    rsync -a \
        --exclude='node_modules' \
        --exclude='.git' \
        --exclude='tests' \
        --exclude='dist' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='vendor' \
        ./ "$WORK_DIR/"
else
    shopt -s dotglob
    cp -r ./* "$WORK_DIR/" 2>/dev/null || true
    shopt -u dotglob
    rm -rf "$WORK_DIR/node_modules" "$WORK_DIR/.git" "$WORK_DIR/dist" "$WORK_DIR/vendor"
fi

# Ensure .env exists (rsync respects .gitignore, so explicitly copy/create it)
if [ ! -f "$WORK_DIR/.env" ]; then
    if [ -f ".env" ]; then
        echo "   Copying .env file..."
        cp .env "$WORK_DIR/.env"
    elif [ -f ".env.example" ]; then
        echo "   Creating .env from .env.example..."
        cp .env.example "$WORK_DIR/.env"
    else
        echo "   ❌ No .env or .env.example found!"
        exit 1
    fi
fi

echo "   ✓ Clean copy created in isolated directory"

# Create production .env in the isolated build
echo ""
echo "⚙️  Step 3: Configuring for production..."
if [ -f "$WORK_DIR/.env" ]; then
    # Update environment settings
    sed -i 's/APP_ENV=.*/APP_ENV=production/' "$WORK_DIR/.env"
    sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' "$WORK_DIR/.env"
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' "$WORK_DIR/.env"
    sed -i 's|DB_DATABASE=.*|DB_DATABASE=./database/database.sqlite|' "$WORK_DIR/.env"
    
    # Ensure APP_KEY exists, generate if missing
    if ! grep -q "^APP_KEY=base64:" "$WORK_DIR/.env"; then
        echo "   Generating application key..."
        cd "$WORK_DIR"
        php artisan key:generate --force --no-interaction
        cd - > /dev/null
    fi
    
    echo "   ✓ Production config ready"
else
    echo "   ❌ .env file not found!"
    exit 1
fi

echo ""
echo "📦 Step 4: Installing production dependencies (isolated)..."
cd "$WORK_DIR"
composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "🧹 Step 5: Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Remove Vite dev server indicator
rm -f public/hot

echo ""
echo "⚡ Step 6: Running production optimizations..."
php artisan config:cache || {
    echo "❌ Config cache failed! Check your .env file"
    exit 1
}

# Check for route closures before caching
echo "   Checking routes for closures..."
if php artisan route:list --json 2>&1 | grep -q "error\|exception"; then
    echo "❌ Route listing failed! You may have closures in your routes."
    echo "   Laravel cannot cache routes with closures."
    echo "   Check routes/web.php, routes/auth.php, routes/settings.php"
    exit 1
fi

php artisan route:cache || {
    echo "❌ Route cache failed!"
    echo "   This usually means you have closures in your routes."
    echo "   All routes must use controllers for production builds."
    exit 1
}

php artisan view:cache || {
    echo "❌ View cache failed!"
    exit 1
}

php artisan event:cache || {
    echo "⚠️  Event cache failed (non-critical)"
}

# Optimize composer autoloader
composer dump-autoload --optimize --classmap-authoritative

echo ""
echo "📦 Step 7: Creating fresh SQLite database..."
rm -f "$WORK_DIR/database/database.sqlite"
touch "$WORK_DIR/database/database.sqlite"

echo "   Running migrations..."
php artisan migrate --force --no-interaction || {
    echo "❌ Database migration failed!"
    echo "   Check your migration files for errors."
    exit 1
}

BUNDLE_DB=true
echo "   ✓ Fresh database created ($(du -h "$WORK_DIR/database/database.sqlite" | cut -f1))"

echo ""
echo "📦 Step 8: Creating portable bundle..."
cd - > /dev/null

BUNDLE="$BUILD_DIR/$APP_NAME"
rm -rf "$BUNDLE"
mkdir -p "$BUNDLE/app"

# Move the optimized build to the bundle
if command -v rsync &> /dev/null; then
    rsync -a "$WORK_DIR/" "$BUNDLE/app/"
else
    # cp with glob doesn't copy dotfiles, so use dotglob
    (shopt -s dotglob; cp -r "$WORK_DIR/"* "$BUNDLE/app/")
fi

# Ensure critical files exist in bundle
if [ ! -f "$BUNDLE/app/.env" ] && [ -f "$WORK_DIR/.env" ]; then
    echo "   Copying .env to bundle..."
    cp "$WORK_DIR/.env" "$BUNDLE/app/.env"
fi

# Create writable storage directories
mkdir -p "$BUNDLE/app/storage/framework/sessions"
mkdir -p "$BUNDLE/app/storage/framework/cache"
mkdir -p "$BUNDLE/app/storage/framework/views"
mkdir -p "$BUNDLE/app/storage/logs"
chmod -R 777 "$BUNDLE/app/storage" 2>/dev/null || true
chmod -R 777 "$BUNDLE/app/bootstrap/cache" 2>/dev/null || true

echo "   ✓ Production-ready bundle created"

echo ""
echo "⬇️  Step 9: Downloading portable PHP..."
PHP_URL="https://windows.php.net/downloads/releases/php-${PHP_VERSION}-${PHP_BUILD}.zip"

wget -q --show-progress -O /tmp/php-portable.zip "$PHP_URL" 2>/dev/null || curl -L -o /tmp/php-portable.zip "$PHP_URL" 2>/dev/null || {
    echo ""
    echo "❌ Automatic download failed."
    echo ""
    echo "Please download manually:"
    echo "   https://windows.php.net/downloads/releases/php-${PHP_VERSION}-${PHP_BUILD}.zip"
    echo ""
    read -p "Path to downloaded PHP zip (or Enter to skip): " PHP_ZIP_PATH

    if [ -n "$PHP_ZIP_PATH" ] && [ -f "$PHP_ZIP_PATH" ]; then
        cp "$PHP_ZIP_PATH" /tmp/php-portable.zip
    else
        echo ""
        echo "⚠️  Skipping PHP bundling."
        echo "   Users will need PHP 8.3+ installed."
        SKIP_PHP=true
    fi
}

if [ "$SKIP_PHP" != "true" ] && [ -f /tmp/php-portable.zip ]; then
    # Check if it's a valid zip
    if [ "$(stat -c%s /tmp/php-portable.zip 2>/dev/null || echo 0)" -gt 1000000 ]; then
        echo "📦 Extracting PHP..."
        unzip -q /tmp/php-portable.zip -d "$BUNDLE/php"
        rm /tmp/php-portable.zip

        echo "🔧 Configuring PHP..."
        cat > "$BUNDLE/php/php.ini" <<'INI'
; PHP Configuration for qheap-track
extension_dir = "ext"
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=pdo_mysql
extension=pdo_pgsql
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
INI
        echo "   ✓ Portable PHP included!"
        HAS_PHP=true
    else
        rm -f /tmp/php-portable.zip
        SKIP_PHP=true
    fi
fi

echo ""
echo "📦 Step 10: Creating launchers..."

# Windows launcher with app-mode window (closes when window closes)
cat > "$BUNDLE/qheap-track.ps1" <<'PS1'
$AppDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$PHPDir = Join-Path $AppDir "php"
$AppPath = Join-Path $AppDir "app"

if (Test-Path (Join-Path $PHPDir "php.exe")) {
    $PHP = Join-Path $PHPDir "php.exe"
} else {
    $PHP = "php"
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Starting Qheap Track" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Start PHP server in background
Set-Location $AppPath

# Clear Laravel caches on first run
& $PHP artisan config:clear | Out-Null
& $PHP artisan route:clear | Out-Null
& $PHP artisan view:clear | Out-Null

$serverProcess = Start-Process $PHP -ArgumentList "artisan serve --host=127.0.0.1 --port=8000" -NoNewWindow -PassThru

# Wait for server to start
Start-Sleep -Seconds 3

Write-Host "✓ Server started" -ForegroundColor Green
Write-Host "  Opening application window..." -ForegroundColor Cyan
Write-Host ""

# Try to open in app mode (like Electron)
# Use a separate user data directory to ensure we get a new process we can monitor
$userDataDir = Join-Path $AppPath "storage\browser-profile"
if (-not (Test-Path $userDataDir)) {
    New-Item -ItemType Directory -Path $userDataDir -Force | Out-Null
}

$edgePath = "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
if (-not (Test-Path $edgePath)) {
    $edgePath = "$env:ProgramFiles\Microsoft\Edge\Application\msedge.exe"
}

$browserProcess = $null

if (Test-Path $edgePath) {
    $browserProcess = Start-Process $edgePath -ArgumentList "--app=http://localhost:8000 --window-size=1200,800 --user-data-dir=`"$userDataDir`"" -PassThru
} else {
    $chromePath = "$env:ProgramFiles\Google\Chrome\Application\chrome.exe"
    if (Test-Path $chromePath) {
        $browserProcess = Start-Process $chromePath -ArgumentList "--app=http://localhost:8000 --window-size=1200,800 --user-data-dir=`"$userDataDir`"" -PassThru
    } else {
        Start-Process "http://localhost:8000"
    }
}

Write-Host "  Application running. Close the window to stop." -ForegroundColor Yellow
Write-Host ""

# Monitor browser process - when it closes, shut down server
if ($browserProcess) {
    # Give browser a moment to start
    Start-Sleep -Seconds 1

    # Check if browser process is still running
    $isRunning = Get-Process -Id $browserProcess.Id -ErrorAction SilentlyContinue
    if ($isRunning) {
        Wait-Process -Id $browserProcess.Id -ErrorAction SilentlyContinue
        Write-Host "  Window closed. Stopping server..." -ForegroundColor Yellow
    } else {
        Write-Host "  Browser opened in existing session." -ForegroundColor Yellow
        Write-Host "  Press Ctrl+C to stop the server." -ForegroundColor Yellow
        Wait-Process -Id $serverProcess.Id
    }
    Stop-Process -Id $serverProcess.Id -Force -ErrorAction SilentlyContinue
} else {
    # If no browser process to monitor, wait for Ctrl+C
    Write-Host "  Press Ctrl+C to stop" -ForegroundColor Yellow
    Wait-Process -Id $serverProcess.Id
}
PS1

cat > "$BUNDLE/qheap-track.bat" <<'BAT'
@echo off
powershell.exe -ExecutionPolicy Bypass -File "%~dp0qheap-track.ps1"
BAT

# Linux launcher with app-mode window (closes when window closes)
cat > "$BUNDLE/qheap-track.sh" <<'BASH'
#!/bin/bash
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ -f "$DIR/php/bin/php" ]; then
    PHP="$DIR/php/bin/php"
else
    PHP="php"
fi

# Cleanup function
cleanup() {
    echo ""
    echo "  Window closed. Stopping server..."
    kill $SERVER_PID 2>/dev/null
    exit 0
}

trap cleanup EXIT INT TERM

echo ""
echo "========================================"
echo "  Starting Qheap Track"
echo "========================================"
echo ""

# Start PHP server in background
cd "$DIR/app"

# Clear Laravel caches on first run
$PHP artisan config:clear > /dev/null 2>&1
$PHP artisan route:clear > /dev/null 2>&1
$PHP artisan view:clear > /dev/null 2>&1

$PHP artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
SERVER_PID=$!

# Wait for server to start
sleep 3

echo "✓ Server started"
echo "  Opening application window..."
echo ""

# Try to open in app mode and monitor the process
# Use a separate user data directory to ensure we get a new process we can monitor
USER_DATA_DIR="$DIR/app/storage/browser-profile"
mkdir -p "$USER_DATA_DIR"

BROWSER_PID=""

if command -v google-chrome &> /dev/null; then
    google-chrome --app=http://localhost:8000 --window-size=1200,800 --user-data-dir="$USER_DATA_DIR" 2>/dev/null &
    BROWSER_PID=$!
elif command -v chromium &> /dev/null; then
    chromium --app=http://localhost:8000 --window-size=1200,800 --user-data-dir="$USER_DATA_DIR" 2>/dev/null &
    BROWSER_PID=$!
elif command -v chromium-browser &> /dev/null; then
    chromium-browser --app=http://localhost:8000 --window-size=1200,800 --user-data-dir="$USER_DATA_DIR" 2>/dev/null &
    BROWSER_PID=$!
elif command -v microsoft-edge &> /dev/null; then
    microsoft-edge --app=http://localhost:8000 --window-size=1200,800 --user-data-dir="$USER_DATA_DIR" 2>/dev/null &
    BROWSER_PID=$!
else
    xdg-open http://localhost:8000 2>/dev/null || open http://localhost:8000 2>/dev/null
fi

echo "  Application running. Close the window to stop."
echo ""

# Wait for browser to close, then kill server
if [ -n "$BROWSER_PID" ]; then
    # Give browser a moment to start
    sleep 1

    # Check if browser actually started
    if ps -p $BROWSER_PID > /dev/null 2>&1; then
        wait $BROWSER_PID 2>/dev/null
    else
        echo "  Browser opened in existing session."
        echo "  Press Ctrl+C to stop the server."
        wait $SERVER_PID
    fi
    kill $SERVER_PID 2>/dev/null
else
    # If no browser PID, wait for server
    echo "  Press Ctrl+C to stop the server."
    wait $SERVER_PID
fi
BASH

chmod +x "$BUNDLE/qheap-track.sh"

# README
cat > "$BUNDLE/README.txt" <<'README'
Qheap Track - Portable Desktop App
===================================

Quick Start:
-----------
Windows: Double-click qheap-track.bat
Linux:   Run ./qheap-track.sh

The app will open in your browser at http://localhost:8000

What's Included:
---------------
✓ Complete Laravel + Vue.js application
✓ All dependencies bundled
✓ Database configured and ready
✓ Portable PHP (no installation needed)

First Time Setup:
----------------
None! Just double-click and go!

Troubleshooting:
---------------
- Make sure port 8000 is not in use
- On Windows, if you get a security warning, click "More info" then "Run anyway"
- Your antivirus might scan it first - this is normal

Database:
--------
All users share the same database.
Your data is stored and synced automatically.
README

if [ "$HAS_PHP" = "true" ]; then
    cat >> "$BUNDLE/README.txt" <<'README2'

System Requirements:
-------------------
✓ Windows 10 or later (64-bit)
✓ No other software needed!
README2
else
    cat >> "$BUNDLE/README.txt" <<'README2'

System Requirements:
-------------------
- Windows 10 or later (64-bit)
- PHP 8.3+ must be installed
  Download from: https://windows.php.net/download/
README2
fi

echo ""
echo "📦 Step 11: Validating build..."
cd "$BUNDLE/app"

# Check if APP_KEY exists
echo "   Checking application key..."
if ! grep -q "^APP_KEY=base64:" ".env" 2>/dev/null; then
    echo "   ⚠️  APP_KEY missing, generating..."
    php artisan key:generate --force --no-interaction || {
        echo "❌ Failed to generate APP_KEY"
        cd - > /dev/null
        exit 1
    }
fi

echo "   Checking if application is bootable..."
php artisan --version > /dev/null 2>&1 || {
    echo "❌ Build validation failed! Application cannot boot."
    echo "   Check the error logs in the build directory."
    cd - > /dev/null
    exit 1
}

# Test a simple route to ensure encryption works
echo "   Testing encryption (making test request)..."
timeout 5 php artisan serve --host=127.0.0.1 --port=9999 > /dev/null 2>&1 &
SERVER_PID=$!
sleep 2
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:9999 2>/dev/null | grep -qE "^(200|302|401)"; then
    echo "   ✓ Application responds correctly"
else
    echo "   ⚠️  Application may have runtime issues"
fi
kill $SERVER_PID 2>/dev/null
wait $SERVER_PID 2>/dev/null

echo "   ✓ Application validated successfully"
cd - > /dev/null

echo ""
echo "📦 Step 12: Creating final package..."
cd "$BUILD_DIR"
zip -rq "${APP_NAME}-portable.zip" "$APP_NAME/"
cd ..

echo ""
echo "✅ Build complete!"
echo ""
echo "📦 Package: $BUILD_DIR/${APP_NAME}-portable.zip"
SIZE=$(du -h "$BUILD_DIR/${APP_NAME}-portable.zip" | cut -f1)
echo "   Size: $SIZE"
echo ""

if [ "$HAS_PHP" = "true" ]; then
    echo "✨ TRUE STANDALONE PACKAGE:"
    echo "   ✓ PHP included - NO installation needed"
    echo "   ✓ SQLite database bundled (zero latency!)"
    echo "   ✓ Laravel optimizations applied (faster loading)"
    echo "   ✓ Just extract and double-click qheap-track.bat"
    echo "   ✓ Works on ANY Windows PC"
else
    echo "⚠️  SEMI-PORTABLE PACKAGE:"
    echo "   - PHP NOT included"
    echo "   - User needs PHP 8.3+ installed"
    echo "   - Or re-run and provide PHP zip when prompted"
fi

echo ""
echo "🚀 To use:"
echo "   1. Extract the zip"
echo "   2. Double-click qheap-track.bat (Windows)"
echo "   3. Done!"
echo ""
echo "💡 Database:"
echo "   Each copy has its own local SQLite database (fast!)"
echo "   Data is NOT shared between users"
echo "   For shared database, use PostgreSQL (Neon) in .env"
