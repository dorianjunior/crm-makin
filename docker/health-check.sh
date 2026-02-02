#!/bin/sh
# ==============================================================================
# Docker Container Health Check Script
# ==============================================================================
# This script checks if the Laravel application is healthy by:
# 1. Checking if PHP-FPM is running
# 2. Testing database connectivity
# 3. Verifying Redis connection
# ==============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

echo "🔍 Running health checks..."

# Check 1: PHP-FPM process
if ! pgrep -x php-fpm > /dev/null; then
    echo "${RED}❌ PHP-FPM is not running${NC}"
    exit 1
fi
echo "${GREEN}✓${NC} PHP-FPM is running"

# Check 2: Application root exists
if [ ! -f /var/www/html/artisan ]; then
    echo "${RED}❌ Laravel application not found${NC}"
    exit 1
fi
echo "${GREEN}✓${NC} Laravel application found"

# Check 3: Storage directory is writable
if [ ! -w /var/www/html/storage ]; then
    echo "${RED}❌ Storage directory is not writable${NC}"
    exit 1
fi
echo "${GREEN}✓${NC} Storage directory is writable"

# Check 4: Database connection (optional, only in production)
if [ "$APP_ENV" = "production" ]; then
    if ! php /var/www/html/artisan db:show > /dev/null 2>&1; then
        echo "${RED}❌ Database connection failed${NC}"
        exit 1
    fi
    echo "${GREEN}✓${NC} Database connection successful"
fi

# Check 5: Redis connection
if ! php /var/www/html/artisan redis:ping > /dev/null 2>&1; then
    echo "${RED}❌ Redis connection failed${NC}"
    exit 1
fi
echo "${GREEN}✓${NC} Redis connection successful"

echo "${GREEN}✅ All health checks passed${NC}"
exit 0
