#!/bin/bash

# EDUTN PRO System Verification Script
# This script verifies that all components are properly installed and configured

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   EDUTN PRO - System Verification Script          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""

cd edutnpro-backend

# Function to check component
check_component() {
    local name=$1
    local command=$2
    local description=$3

    echo -ne "  ${description}... "

    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((FAILED++))
        return 1
    fi
}

# Function to check file exists
check_file() {
    local file=$1
    local description=$2

    echo -ne "  ${description}... "

    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ EXISTS${NC}"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗ MISSING${NC}"
        ((FAILED++))
        return 1
    fi
}

# Function to count items
count_items() {
    local command=$1
    local description=$2
    local min_count=$3

    echo -ne "  ${description}... "

    local count=$(eval "$command" 2>/dev/null | wc -l)

    if [ "$count" -ge "$min_count" ]; then
        echo -e "${GREEN}✓ $count items${NC}"
        ((PASSED++))
        return 0
    else
        echo -e "${YELLOW}⚠ Only $count items (expected >= $min_count)${NC}"
        ((WARNINGS++))
        return 1
    fi
}

echo -e "${YELLOW}[1/8] Checking PHP & Laravel...${NC}"
check_component "PHP Version" "php -v | grep -q 'PHP 8'" "PHP 8.x installed"
check_component "Composer" "composer --version" "Composer installed"
check_component "Laravel" "php artisan --version" "Laravel functioning"
echo ""

echo -e "${YELLOW}[2/8] Checking Artisan Commands...${NC}"
check_component "edutn:init" "php artisan list | grep -q 'edutn:init'" "edutn:init command registered"
check_component "edutn:setup-gamification" "php artisan list | grep -q 'edutn:setup-gamification'" "edutn:setup-gamification command registered"
check_component "edutn:setup-social" "php artisan list | grep -q 'edutn:setup-social'" "edutn:setup-social command registered"
echo ""

echo -e "${YELLOW}[3/8] Checking Database Seeders...${NC}"
check_file "database/seeders/BadgeSeeder.php" "BadgeSeeder exists"
check_file "database/seeders/GamificationSeeder.php" "GamificationSeeder exists"
check_file "database/seeders/SocialLearningSeeder.php" "SocialLearningSeeder exists"
check_file "database/seeders/DemoDataSeeder.php" "DemoDataSeeder exists"
echo ""

echo -e "${YELLOW}[4/8] Checking Controllers...${NC}"
check_file "app/Http/Controllers/AdminGamificationController.php" "AdminGamificationController exists"
check_file "app/Http/Controllers/AnalyticsController.php" "AnalyticsController exists"
echo ""

echo -e "${YELLOW}[5/8] Checking Admin Routes...${NC}"
count_items "php artisan route:list --path=admin | grep gamification" "Gamification routes registered" 2
count_items "php artisan route:list --path=admin | grep badges" "Badge routes registered" 4
count_items "php artisan route:list --path=admin | grep challenges" "Challenge routes registered" 4
count_items "php artisan route:list --path=admin | grep analytics" "Analytics routes registered" 3
echo ""

echo -e "${YELLOW}[6/8] Checking Models...${NC}"
check_file "app/Models/Badge.php" "Badge model exists"
check_file "app/Models/Challenge.php" "Challenge model exists"
check_file "app/Models/StudentAchievement.php" "StudentAchievement model exists"
check_file "app/Models/StudyGroup.php" "StudyGroup model exists"
check_file "app/Models/TutorProfile.php" "TutorProfile model exists"
echo ""

echo -e "${YELLOW}[7/8] Checking Deployment Scripts...${NC}"
check_file "../deploy.sh" "deploy.sh exists"
check_component "Deploy script executable" "test -x ../deploy.sh" "deploy.sh is executable"
echo ""

echo -e "${YELLOW}[8/8] Checking Documentation...${NC}"
check_file "../QUICK_START.md" "QUICK_START.md exists"
check_file "../README_PRODUCTION.md" "README_PRODUCTION.md exists"
check_file "../PRODUCTION_IMMEDIATE.md" "PRODUCTION_IMMEDIATE.md exists"
echo ""

# Summary
echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Verification Summary                             ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${GREEN}✓ Passed:${NC}   $PASSED"
echo -e "  ${RED}✗ Failed:${NC}   $FAILED"
echo -e "  ${YELLOW}⚠ Warnings:${NC} $WARNINGS"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║   ✓ ALL CHECKS PASSED!                            ║${NC}"
    echo -e "${GREEN}║   System is ready for deployment!                  ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}Next steps:${NC}"
    echo -e "  1. Configure your .env file"
    echo -e "  2. Run: ${YELLOW}./deploy.sh${NC}"
    echo -e "  3. Or manually: ${YELLOW}php artisan edutn:init --demo${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}╔════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║   ✗ SOME CHECKS FAILED!                           ║${NC}"
    echo -e "${RED}║   Please fix the issues above before deploying.    ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════╝${NC}"
    echo ""
    exit 1
fi
