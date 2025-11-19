#!/bin/bash

# EDUTN PRO - Deployment Script
# Automates complete deployment of EDUTN PRO system

set -e  # Exit on error

echo "TPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPW"
echo "Q     =€ EDUTN PRO - Automated Deployment Script     Q"
echo "ZPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP]"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKEND_DIR="$PROJECT_DIR/edutnpro-backend"

echo -e "${BLUE}=Á Project Directory: $PROJECT_DIR${NC}"
echo ""

# Step 1: Backend Setup
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo -e "${BLUE}Step 1: Backend Setup${NC}"
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo ""

cd "$BACKEND_DIR"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}   .env file not found. Copying from .env.example...${NC}"
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN} .env file created${NC}"
    else
        echo -e "${RED}L .env.example not found. Please create .env manually.${NC}"
        exit 1
    fi
else
    echo -e "${GREEN} .env file exists${NC}"
fi

# Install dependencies
echo ""
echo -e "${BLUE}=æ Installing Composer dependencies...${NC}"
composer install --optimize-autoloader --no-dev
echo -e "${GREEN} Dependencies installed${NC}"

# Generate app key if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo ""
    echo -e "${BLUE}= Generating application key...${NC}"
    php artisan key:generate
    echo -e "${GREEN} Application key generated${NC}"
fi

# Clear and optimize
echo ""
echo -e "${BLUE}>ù Clearing and optimizing cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN} Cache cleared${NC}"

# Database setup
echo ""
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo -e "${BLUE}Step 2: Database Setup${NC}"
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo ""

# Check database connection
echo -e "${BLUE}= Testing database connection...${NC}"
if php artisan migrate:status &>/dev/null; then
    echo -e "${GREEN} Database connection successful${NC}"
else
    echo -e "${RED}L Database connection failed. Please check your .env configuration.${NC}"
    exit 1
fi

# Run migrations
echo ""
echo -e "${BLUE}=Ä  Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN} Migrations completed${NC}"

# Initialize EDUTN PRO System
echo ""
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo -e "${BLUE}Step 3: EDUTN PRO Initialization${NC}"
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo ""

# Ask for demo data
echo -e "${YELLOW}Do you want to include demo data? (yes/no)${NC}"
read -p "> " INCLUDE_DEMO

if [[ "$INCLUDE_DEMO" =~ ^[Yy]([Ee][Ss])?$ ]]; then
    echo ""
    echo -e "${BLUE}<® Initializing EDUTN PRO with demo data...${NC}"
    php artisan edutn:init --demo
else
    echo ""
    echo -e "${BLUE}<® Initializing EDUTN PRO...${NC}"
    php artisan edutn:init
fi
echo -e "${GREEN} EDUTN PRO initialized${NC}"

# Optimize for production
echo ""
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo -e "${BLUE}Step 4: Production Optimization${NC}"
echo -e "${BLUE}PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP${NC}"
echo ""

echo -e "${BLUE}¡ Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN} Optimization complete${NC}"

# Storage link
echo ""
echo -e "${BLUE}= Creating storage link...${NC}"
php artisan storage:link
echo -e "${GREEN} Storage link created${NC}"

# File permissions
echo ""
echo -e "${BLUE}= Setting file permissions...${NC}"
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN} Permissions set${NC}"

# Final summary
echo ""
echo "TPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPW"
echo "Q       <‰ DEPLOYMENT COMPLETED SUCCESSFULLY! <‰       Q"
echo "ZPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP]"
echo ""

echo -e "${GREEN} EDUTN PRO is now ready for production!${NC}"
echo ""
echo -e "${BLUE}=Ê Deployment Summary:${NC}"
echo "   " Backend Dependencies:  Installed"
echo "   " Database Migrations:  Completed"
echo "   " Gamification System:  Initialized"
echo "   " Social Learning:  Initialized"
echo "   " Production Optimized:  Complete"
echo ""
echo -e "${BLUE}< API Endpoints Available:${NC}"
echo "   " Gamification: /api/student/achievement"
echo "   " Badges: /api/student/badges"
echo "   " Leaderboard: /api/student/leaderboard"
echo "   " Study Groups: /api/student/study-groups"
echo "   " Forums: /api/student/forums"
echo "   " Resources: /api/student/resources"
echo ""
echo -e "${YELLOW}=Ú Next Steps:${NC}"
echo "   1. Configure your web server (Nginx/Apache)"
echo "   2. Set up SSL certificate"
echo "   3. Configure your domain DNS"
echo "   4. Test the API endpoints"
echo "   5. Deploy Flutter mobile apps"
echo ""
echo -e "${GREEN}=Ö Documentation:${NC}"
echo "   " GAMIFICATION.md - Gamification system guide"
echo "   " IMPLEMENTATION_SUMMARY.md - Complete feature overview"
echo "   " QUICK_START.md - Quick start guide"
echo ""
echo "=€ Happy deploying! EDUTN PRO is ready to revolutionize education!"
