#!/bin/bash
# Quick build diagnostic script
# Run this to check if your app is ready for production build

echo "🔍 Build Readiness Diagnostic"
echo "=============================="
echo ""

ISSUES=0

# Check PHP
echo "1. Checking PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    echo "   ✓ $PHP_VERSION"
else
    echo "   ❌ PHP not found"
    ISSUES=$((ISSUES + 1))
fi

# Check Composer
echo ""
echo "2. Checking Composer..."
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version --no-ansi | head -n 1)
    echo "   ✓ $COMPOSER_VERSION"
else
    echo "   ❌ Composer not found"
    ISSUES=$((ISSUES + 1))
fi

# Check Bun
echo ""
echo "3. Checking Bun..."
if command -v bun &> /dev/null; then
    BUN_VERSION=$(bun --version)
    echo "   ✓ Bun $BUN_VERSION"
else
    echo "   ❌ Bun not found"
    ISSUES=$((ISSUES + 1))
fi

# Check routes for closures
echo ""
echo "4. Checking routes for closures..."
if ! php artisan route:list > /dev/null 2>&1; then
    echo "   ❌ Cannot list routes - there may be an error in your routes"
    echo "      Run: php artisan route:list"
    ISSUES=$((ISSUES + 1))
else
    # Check for problematic closures in web routes (not console.php)
    PROBLEMATIC=$(grep -r "Route::" routes/*.php 2>/dev/null | grep -v "routes/console.php" | grep -E "function\s*\(" | grep -v "->group\(function" | grep -v "^[[:space:]]*//" || true)
    if [ -n "$PROBLEMATIC" ]; then
        echo "   ⚠️  Found closure(s) in web routes:"
        echo "$PROBLEMATIC"
        echo ""
        echo "   Laravel CANNOT cache routes with closures."
        echo "   Convert them to controller methods."
        ISSUES=$((ISSUES + 1))
    else
        echo "   ✓ No problematic closures in web routes"
    fi
fi

# Check APP_KEY
echo ""
echo "5. Checking application key..."
if [ -f .env ]; then
    if grep -q "^APP_KEY=base64:" .env; then
        echo "   ✓ APP_KEY is set"
    else
        echo "   ❌ APP_KEY is missing or invalid"
        echo "      Run: php artisan key:generate"
        ISSUES=$((ISSUES + 1))
    fi
else
    echo "   ❌ .env file not found"
    ISSUES=$((ISSUES + 1))
fi

# Check config
echo ""
echo "6. Checking configuration..."
CONFIG_OUTPUT=$(php artisan config:cache 2>&1)
if echo "$CONFIG_OUTPUT" | grep -qi "error"; then
    echo "   ❌ Config caching failed"
    echo "      Run: php artisan config:cache"
    ISSUES=$((ISSUES + 1))
else
    echo "   ✓ Config can be cached"
    php artisan config:clear > /dev/null 2>&1
fi

# Check views
echo ""
echo "7. Checking views..."
VIEW_OUTPUT=$(php artisan view:cache 2>&1)
if echo "$VIEW_OUTPUT" | grep -qi "error"; then
    echo "   ❌ View caching failed"
    echo "      Run: php artisan view:cache"
    ISSUES=$((ISSUES + 1))
else
    echo "   ✓ Views can be cached"
    php artisan view:clear > /dev/null 2>&1
fi

# Check migrations
echo ""
echo "8. Checking migrations..."
if php artisan migrate:status > /dev/null 2>&1; then
    echo "   ✓ Migrations are valid"
else
    echo "   ⚠️  Cannot check migration status"
    echo "      Your database might not be set up"
fi

# Check for common issues
echo ""
echo "9. Checking for common issues..."

# Check if .env exists
if [ ! -f .env.example ]; then
    echo "   ⚠️  .env.example not found"
fi

# Check for vendor directory
if [ ! -d vendor ]; then
    echo "   ❌ vendor/ directory not found. Run: composer install"
    ISSUES=$((ISSUES + 1))
else
    echo "   ✓ Vendor directory exists"
fi

# Check for node_modules
if [ ! -d node_modules ]; then
    echo "   ❌ node_modules/ directory not found. Run: bun install"
    ISSUES=$((ISSUES + 1))
else
    echo "   ✓ Node modules installed"
fi

# Check if build assets exist
if [ ! -d public/build ]; then
    echo "   ⚠️  public/build not found. Frontend may not be built."
    echo "      Run: bun run build"
fi

# Summary
echo ""
echo "=============================="
if [ $ISSUES -eq 0 ]; then
    echo "✅ All checks passed!"
    echo "   Your app should be ready for production build."
    echo ""
    echo "   Run: ./build.sh"
else
    echo "❌ Found $ISSUES issue(s)"
    echo "   Fix the issues above before building."
fi
echo ""

