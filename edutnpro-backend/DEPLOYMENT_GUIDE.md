# 🚀 Guide de Déploiement - EDUTN PRO

## Guide Complet d'Installation et de Déploiement

Ce guide vous accompagne étape par étape pour déployer EDUTN PRO sur votre serveur de production.

---

## 📋 Prérequis

### Serveur Requis
- **OS**: Ubuntu 22.04 LTS (recommandé) ou Debian 11+
- **RAM**: Minimum 2 GB (4 GB recommandé pour production)
- **Disque**: 20 GB minimum
- **CPU**: 2 cores minimum

### Logiciels Requis
- PHP 8.2 ou supérieur
- MySQL 8.0 ou MariaDB 10.11+
- Nginx ou Apache
- Composer 2.x
- Node.js 18+ et NPM (pour les assets)
- Git
- Redis (optionnel, pour le cache)

---

## 🔧 Installation sur Ubuntu 22.04

### 1. Mise à jour du système

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Installation de PHP 8.2 et extensions

```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
php8.2-bcmath php8.2-intl php8.2-redis -y
```

### 3. Installation de MySQL

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

Créer la base de données :

```bash
sudo mysql -u root -p

CREATE DATABASE edutnpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'edutnpro_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON edutnpro.* TO 'edutnpro_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Installation de Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### 5. Installation de Nginx

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 6. Installation de Node.js et NPM

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 7. Installation de Redis (optionnel)

```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

---

## 📥 Déploiement de l'Application

### 1. Cloner le repository

```bash
cd /var/www
sudo git clone https://github.com/haythemsaa/edutn.git
cd edutn/edutnpro-backend
```

### 2. Configuration des permissions

```bash
sudo chown -R www-data:www-data /var/www/edutn
sudo chmod -R 755 /var/www/edutn
sudo chmod -R 775 /var/www/edutn/edutnpro-backend/storage
sudo chmod -R 775 /var/www/edutn/edutnpro-backend/bootstrap/cache
```

### 3. Installation des dépendances PHP

```bash
composer install --optimize-autoloader --no-dev
```

### 4. Configuration de l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Éditer le fichier `.env` :

```bash
sudo nano .env
```

Configurer les paramètres :

```env
APP_NAME="EDUTN PRO"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edutnpro
DB_USERNAME=edutnpro_user
DB_PASSWORD=VOTRE_MOT_DE_PASSE_FORT

CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-serveur.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@edutnpro.tn
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Exécution des migrations et seeders

```bash
php artisan migrate --force
php artisan db:seed --force
```

**Credentials par défaut créés :**
- Email: `admin@edutnpro.tn`
- Password: `password`

⚠️ **IMPORTANT**: Changez ce mot de passe immédiatement après la première connexion !

### 6. Optimisation pour la production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. Installation des assets frontend

```bash
npm install
npm run build
```

### 8. Lier le storage public

```bash
php artisan storage:link
```

---

## 🌐 Configuration Nginx

Créer un fichier de configuration :

```bash
sudo nano /etc/nginx/sites-available/edutnpro
```

Contenu :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name edutnpro.tn www.edutnpro.tn;

    # Redirection HTTPS (après configuration SSL)
    # return 301 https://$server_name$request_uri;

    root /var/www/edutn/edutnpro-backend/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
}
```

Activer le site :

```bash
sudo ln -s /etc/nginx/sites-available/edutnpro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Configuration SSL avec Let's Encrypt

### 1. Installation de Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Obtenir un certificat SSL

```bash
sudo certbot --nginx -d edutnpro.tn -d www.edutnpro.tn
```

Suivez les instructions interactives.

### 3. Renouvellement automatique

Certbot configure automatiquement un cron job. Testez-le :

```bash
sudo certbot renew --dry-run
```

---

## ⏰ Configuration des Tâches Planifiées (Cron)

Éditer le crontab :

```bash
sudo crontab -e -u www-data
```

Ajouter :

```cron
* * * * * cd /var/www/edutn/edutnpro-backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 Configuration des Queues (Workers)

### 1. Installation de Supervisor

```bash
sudo apt install supervisor -y
```

### 2. Créer la configuration du worker

```bash
sudo nano /etc/supervisor/conf.d/edutnpro-worker.conf
```

Contenu :

```ini
[program:edutnpro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/edutn/edutnpro-backend/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/edutn/edutnpro-backend/storage/logs/worker.log
stopwaitsecs=3600
```

### 3. Activer et démarrer

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start edutnpro-worker:*
```

---

## 🔥 Configuration du Pare-feu (UFW)

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

---

## 📊 Monitoring et Logs

### Logs Laravel

```bash
tail -f /var/www/edutn/edutnpro-backend/storage/logs/laravel.log
```

### Logs Nginx

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### Logs PHP

```bash
sudo tail -f /var/log/php8.2-fpm.log
```

---

## 🔄 Mise à Jour de l'Application

```bash
cd /var/www/edutn/edutnpro-backend

# Mettre l'application en mode maintenance
php artisan down

# Récupérer les dernières modifications
git pull origin main

# Installer les dépendances
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Exécuter les migrations
php artisan migrate --force

# Nettoyer et optimiser
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redémarrer les workers
sudo supervisorctl restart edutnpro-worker:*

# Remettre l'application en ligne
php artisan up
```

---

## 💾 Sauvegardes Automatiques

### Configuration de Laravel Backup

Éditer le fichier `.env` :

```env
BACKUP_DISK=local
BACKUP_NOTIFICATION_EMAIL=admin@edutnpro.tn
```

Publier la configuration :

```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

Créer un cron pour les sauvegardes quotidiennes :

```bash
0 2 * * * cd /var/www/edutn/edutnpro-backend && php artisan backup:run >> /dev/null 2>&1
```

---

## 🐛 Dépannage

### Erreur 500 - Internal Server Error

1. Vérifier les logs :
```bash
tail -f storage/logs/laravel.log
```

2. Vérifier les permissions :
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### La page blanche s'affiche

```bash
php artisan config:clear
php artisan cache:clear
```

### Les routes ne fonctionnent pas

```bash
sudo nano /etc/nginx/sites-available/edutnpro
# Vérifier que try_files est correct
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔐 Sécurité Post-Déploiement

### Checklist de Sécurité

- [ ] Changer le mot de passe admin par défaut
- [ ] Configurer APP_DEBUG=false en production
- [ ] Activer HTTPS/SSL
- [ ] Configurer le pare-feu
- [ ] Limiter les tentatives de connexion (rate limiting)
- [ ] Effectuer des sauvegardes régulières
- [ ] Mettre à jour régulièrement le système
- [ ] Surveiller les logs d'erreurs
- [ ] Utiliser des mots de passe forts
- [ ] Activer l'authentification à deux facteurs (2FA)

---

## 📞 Support

Pour toute question ou problème :
- **Email**: support@edutnpro.tn
- **Documentation**: [README_PROJET.md](README_PROJET.md)
- **Repository**: https://github.com/haythemsaa/edutn

---

**© 2025 EDUTN PRO - Développé avec ❤️ pour l'éducation tunisienne**
