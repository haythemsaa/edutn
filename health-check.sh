#!/bin/bash

# EDUTN PRO Production Health Check
# Run this on a deployed system to verify all features are working

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
API_BASE_URL="${1:-http://localhost:8000/api}"
ADMIN_EMAIL="${2:-admin@edutnpro.tn}"
ADMIN_PASSWORD="${3:-password}"

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   EDUTN PRO - Production Health Check             ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  API Base URL: ${YELLOW}$API_BASE_URL${NC}"
echo ""

PASSED=0
FAILED=0

# Function to check endpoint
check_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    local expected_code=${4:-200}
    local auth_token=$5

    echo -ne "  ${description}... "

    local full_url="${API_BASE_URL}${endpoint}"
    local headers=""

    if [ -n "$auth_token" ]; then
        headers="-H \"Authorization: Bearer $auth_token\""
    fi

    local response=$(eval "curl -s -o /dev/null -w '%{http_code}' -X $method $headers $full_url" 2>/dev/null || echo "000")

    if [ "$response" = "$expected_code" ]; then
        echo -e "${GREEN}✓ $response${NC}"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗ $response (expected $expected_code)${NC}"
        ((FAILED++))
        return 1
    fi
}

# Function to check database
check_database() {
    local query=$1
    local description=$2
    local min_count=$3

    echo -ne "  ${description}... "

    cd edutnpro-backend

    local count=$(php artisan tinker --execute="echo $query;" 2>/dev/null | tail -n 1 || echo "0")

    if [ "$count" -ge "$min_count" ]; then
        echo -e "${GREEN}✓ $count records${NC}"
        ((PASSED++))
        cd ..
        return 0
    else
        echo -e "${RED}✗ Only $count records (expected >= $min_count)${NC}"
        ((FAILED++))
        cd ..
        return 1
    fi
}

echo -e "${YELLOW}[1/6] Checking Core System...${NC}"
check_endpoint "GET" "/health" "Health endpoint" 200
check_endpoint "GET" "/student/dashboard" "Student dashboard" 401
echo ""

echo -e "${YELLOW}[2/6] Checking Gamification System...${NC}"
check_endpoint "GET" "/student/achievement" "Student achievements endpoint" 401
check_endpoint "GET" "/student/leaderboard" "Leaderboard endpoint" 200
echo ""

echo -e "${YELLOW}[3/6] Checking Social Learning...${NC}"
check_endpoint "GET" "/student/study-groups" "Study groups endpoint" 401
check_endpoint "GET" "/student/forums" "Forums endpoint" 401
check_endpoint "GET" "/student/tutors" "Tutors endpoint" 401
echo ""

echo -e "${YELLOW}[4/6] Checking Admin Endpoints...${NC}"
check_endpoint "GET" "/admin/gamification/dashboard" "Admin gamification dashboard" 401
check_endpoint "GET" "/admin/badges" "Admin badges list" 401
check_endpoint "GET" "/admin/challenges" "Admin challenges list" 401
check_endpoint "GET" "/admin/analytics/dashboard" "Admin analytics dashboard" 401
echo ""

echo -e "${YELLOW}[5/6] Checking Database Content...${NC}"
if [ -d "edutnpro-backend" ]; then
    check_database "App\\\\Models\\\\Badge::count()" "Badges in database" 10
    check_database "App\\\\Models\\\\Challenge::count()" "Challenges in database" 1
    check_database "App\\\\Models\\\\SubjectForum::count()" "Forums in database" 1
else
    echo -e "  ${YELLOW}⚠ Skipped (not in project directory)${NC}"
fi
echo ""

echo -e "${YELLOW}[6/6] Checking File Permissions...${NC}"
if [ -d "edutnpro-backend" ]; then
    echo -ne "  Storage directory writable... "
    if [ -w "edutnpro-backend/storage" ]; then
        echo -e "${GREEN}✓ YES${NC}"
        ((PASSED++))
    else
        echo -e "${RED}✗ NO${NC}"
        ((FAILED++))
    fi

    echo -ne "  Bootstrap/cache writable... "
    if [ -w "edutnpro-backend/bootstrap/cache" ]; then
        echo -e "${GREEN}✓ YES${NC}"
        ((PASSED++))
    else
        echo -e "${RED}✗ NO${NC}"
        ((FAILED++))
    fi
else
    echo -e "  ${YELLOW}⚠ Skipped (not in project directory)${NC}"
fi
echo ""

# Summary
echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Health Check Summary                             ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${GREEN}✓ Passed:${NC} $PASSED"
echo -e "  ${RED}✗ Failed:${NC} $FAILED"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║   ✓ SYSTEM HEALTHY!                               ║${NC}"
    echo -e "${GREEN}║   All components are functioning correctly.        ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
    exit 0
else
    echo -e "${RED}╔════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║   ⚠ ISSUES DETECTED!                              ║${NC}"
    echo -e "${RED}║   Some components may not be working correctly.    ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${YELLOW}Troubleshooting tips:${NC}"
    echo -e "  1. Check Laravel logs: ${BLUE}tail -f edutnpro-backend/storage/logs/laravel.log${NC}"
    echo -e "  2. Verify .env configuration"
    echo -e "  3. Ensure database is migrated: ${BLUE}php artisan migrate:status${NC}"
    echo -e "  4. Check file permissions: ${BLUE}ls -la edutnpro-backend/storage${NC}"
    echo ""
    exit 1
fi
