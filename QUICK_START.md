# =€ EDUTN PRO - Quick Start Guide

**Get EDUTN PRO running in production in 5 minutes!**

---

## =Ë Prerequisites

Before starting, ensure you have:
-  PHP 8.1 or higher
-  Composer installed
-  MySQL/PostgreSQL database
-  Web server (Nginx or Apache)
-  Git installed

---

## ¡ Option 1: Automated Deployment (Recommended)

**The fastest way to deploy EDUTN PRO:**

### Step 1: Clone Repository

```bash
git clone https://github.com/haythemsaa/edutn.git
cd edutn
```

### Step 2: Configure Environment

```bash
cd edutnpro-backend
cp .env.example .env
```

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edutn_pro
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 3: Run Automated Deployment

```bash
cd ..
./deploy.sh
```

**That's it!** <‰ The script will:
- Install all dependencies
- Run database migrations
- Initialize gamification system (50+ badges)
- Setup social learning platform
- Create demo data (optional)
- Optimize for production

---

## =' Option 2: Manual Deployment

If you prefer manual setup:

### 1. Backend Setup

```bash
cd edutnpro-backend

# Install dependencies
composer install --optimize-autoloader

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
nano .env
```

### 2. Database Setup

```bash
# Run migrations
php artisan migrate

# Initialize EDUTN PRO system
php artisan edutn:init

# Or with demo data
php artisan edutn:init --demo
```

### 3. Production Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

---

## <® Verify Gamification System

Test that gamification is working:

```bash
# Check badges created
php artisan tinker
>>> App\Models\Badge::count()
# Should return 50+

# Check student achievements initialized
>>> App\Models\StudentAchievement::count()
# Should return number of students

# Exit tinker
>>> exit
```

---

## > Verify Social Learning Platform

```bash
# Check subject forums created
php artisan tinker
>>> App\Models\SubjectForum::count()
# Should return number of subjects

>>> exit
```

---

## < Configure Web Server

### Nginx Configuration

Create `/etc/nginx/sites-available/edutn-pro`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/edutn/edutnpro-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/edutn-pro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache Configuration

Create `.htaccess` in public directory (usually auto-generated):

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## = SSL Certificate (Production)

Using Let's Encrypt (Free):

```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is configured automatically
```

---

## >ê Test API Endpoints

### Test Gamification

```bash
# Get student achievement (replace with actual token)
curl -X GET "http://your-domain.com/api/student/achievement" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get all badges
curl -X GET "http://your-domain.com/api/student/badges" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get leaderboard
curl -X GET "http://your-domain.com/api/student/leaderboard?type=class" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test Social Learning

```bash
# Get study groups
curl -X GET "http://your-domain.com/api/student/study-groups" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get forums
curl -X GET "http://your-domain.com/api/student/forums" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get shared resources
curl -X GET "http://your-domain.com/api/student/resources" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## =ñ Flutter Mobile Apps

Deploy the mobile apps:

### Parent App

```bash
cd edutn-parent
flutter pub get
flutter build apk --release
# or for iOS
flutter build ios --release
```

### Student App

```bash
cd edutn-student
flutter pub get
flutter build apk --release
# or for iOS
flutter build ios --release
```

---

## <¯ Quick Commands Reference

### Development

```bash
# Start development server
php artisan serve

# Watch for file changes
php artisan serve --host=0.0.0.0 --port=8000

# Run queue worker
php artisan queue:work

# Run scheduler (cron)
php artisan schedule:run
```

### Maintenance

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Check system status
php artisan about

# List all routes
php artisan route:list

# List all commands
php artisan list
```

### Gamification Management

```bash
# Reinitialize gamification
php artisan edutn:setup-gamification

# Reinitialize social learning
php artisan edutn:setup-social

# Full system reset
php artisan edutn:init --demo
```

### Database

```bash
# Fresh migration (WARNING: deletes all data)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

---

## = Troubleshooting

### Issue: 500 Error

**Solution:**

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan optimize:clear

# Check permissions
chmod -R 775 storage bootstrap/cache
```

### Issue: Database Connection Error

**Solution:**

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check .env configuration
cat .env | grep DB_
```

### Issue: Routes Not Working

**Solution:**

```bash
# Clear route cache
php artisan route:clear

# Re-cache routes
php artisan route:cache

# Check web server configuration
sudo nginx -t
```

### Issue: Storage Files Not Accessible

**Solution:**

```bash
# Recreate storage link
php artisan storage:link

# Check file permissions
ls -la storage/app/public
```

---

## =Ê Production Checklist

Before going live, verify:

- [ ] `.env` configured for production (`APP_ENV=production`, `APP_DEBUG=false`)
- [ ] Database credentials secure
- [ ] SSL certificate installed
- [ ] File permissions set correctly
- [ ] Caches optimized (`config:cache`, `route:cache`, `view:cache`)
- [ ] Storage link created
- [ ] Web server configured and tested
- [ ] Backup strategy in place
- [ ] Monitoring tools configured (optional)
- [ ] Error logging enabled
- [ ] API endpoints tested
- [ ] Mobile apps deployed

---

## <‰ Success Indicators

Your EDUTN PRO installation is successful when:

 API returns JSON responses (not errors)
 Gamification endpoints return badges and achievements
 Social learning endpoints return forums and study groups
 Mobile apps can authenticate and load data
 No errors in Laravel logs

---

## =Ú Next Steps

1. **Import existing data** (if migrating)
2. **Configure school settings** via admin panel
3. **Train staff** on new features
4. **Launch to students** gradually (pilot first)
5. **Monitor analytics** and engagement
6. **Gather feedback** and iterate

---

## <˜ Support & Documentation

- **Full Documentation:** `IMPLEMENTATION_SUMMARY.md`
- **Gamification Guide:** `GAMIFICATION.md`
- **Competitive Analysis:** `ANALYSE_CONCURRENTIELLE_INNOVATION.md`
- **API Reference:** Check routes with `php artisan route:list`

---

## ¡ Performance Tips

### Enable OPcache (PHP)

Edit `/etc/php/8.1/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Configure Queue Workers

Create systemd service `/etc/systemd/system/edutn-worker.service`:

```ini
[Unit]
Description=EDUTN PRO Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/edutn/edutnpro-backend/artisan queue:work

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl enable edutn-worker
sudo systemctl start edutn-worker
```

### Configure Scheduler

Add to crontab (`crontab -e`):

```cron
* * * * * cd /path/to/edutn/edutnpro-backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## < You're All Set!

**EDUTN PRO is now running and ready to revolutionize education in Tunisia!**

Need help? Check the documentation or create an issue on GitHub.

=€ **Happy teaching and learning!** <“
