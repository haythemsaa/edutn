# 🎓 EDUTN PRO - Backend Laravel

## Plateforme de Gestion Scolaire pour la Tunisie

### 📋 Description

EDUTN PRO est une plateforme complète de gestion scolaire développée spécifiquement pour le système éducatif tunisien. Cette application backend Laravel fournit l'API et l'interface web pour gérer tous les aspects d'un établissement scolaire.

### ✨ Fonctionnalités Principales

#### 🏫 Gestion des Établissements
- Multi-établissements avec gestion centralisée
- Structure académique complète (cycles, niveaux, classes)
- Paramètres personnalisables par établissement

#### 👨‍🎓 Gestion des Élèves (SIS)
- Dossiers complets des élèves
- Inscriptions en ligne
- Historique académique
- Suivi personnalisé

#### 📊 Notes et Évaluations
- Saisie rapide des notes
- Calcul automatique des moyennes et classements
- Génération de bulletins PDF
- Système de mentions tunisien (Passable, Assez Bien, Bien, Très Bien, Excellent)
- Support 3 trimestres et notation sur 20

#### 👥 Gestion du Personnel
- Enseignants et personnels administratifs
- Emplois du temps
- Évaluations de performance

#### 💰 Finance et Comptabilité
- Facturation automatique
- Paiements en ligne (E-Dinar, cartes bancaires)
- Comptabilité tunisienne
- Tableaux de bord financiers
- Gestion des bourses et remises

#### 💬 Communication 360°
- Messagerie interne sécurisée
- SMS en masse
- Emails automatisés
- Notifications push (Firebase)
- Annonces ciblées

#### 📝 Examens
- Planification complète
- Convocations automatiques
- Gestion des résultats
- Délibérations

#### 🔐 Sécurité
- Authentification multi-rôles (Laravel Sanctum)
- Permissions granulaires (Spatie Permission)
- Chiffrement SSL/TLS
- Conformité RGPD et lois tunisiennes

### 🛠️ Technologies Utilisées

#### Backend
- **Framework:** Laravel 12.x
- **PHP:** 8.2+
- **Base de données:** MySQL 8.0 / MariaDB 10.11
- **Cache:** Redis 7.0 (optionnel)
- **Queue:** Laravel Queue (Jobs asynchrones)

#### Packages Laravel
- `laravel/sanctum` - Authentification API
- `spatie/laravel-permission` - Gestion des rôles et permissions
- `maatwebsite/excel` - Export/Import Excel
- `barryvdh/laravel-dompdf` - Génération de PDF
- `spatie/laravel-backup` - Sauvegardes automatiques
- `intervention/image` - Traitement d'images

#### Frontend Web (inclus)
- Bootstrap 5.3
- Vue.js 3 (composants dynamiques)
- Chart.js (graphiques)
- DataTables (tableaux interactifs)

### 📦 Installation

#### Prérequis
- PHP 8.2 ou supérieur
- Composer
- MySQL 8.0 ou MariaDB 10.11
- Node.js et NPM (pour les assets frontend)
- Redis (optionnel, pour le cache)

#### Étapes d'Installation

1. **Cloner le repository**
```bash
git clone <repository-url>
cd edutnpro-backend
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**
Éditer le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edutnpro
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

5. **Créer la base de données**
```bash
mysql -u root -p
CREATE DATABASE edutnpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

6. **Exécuter les migrations**
```bash
php artisan migrate
```

7. **Installer les assets frontend**
```bash
npm install
npm run build
```

8. **Lancer le serveur de développement**
```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

### 🗄️ Structure de la Base de Données

Le projet utilise un schéma de base de données complet avec plus de 50 tables :

#### Tables Principales
- `schools` - Établissements scolaires
- `academic_years` - Années scolaires
- `terms` - Trimestres
- `levels` - Niveaux d'enseignement
- `sections` - Sections (Sciences, Lettres, etc.)
- `classes` - Classes
- `students` - Élèves
- `teachers` - Enseignants
- `subjects` - Matières
- `grades` - Notes
- `users` - Utilisateurs
- `roles` - Rôles
- `permissions` - Permissions

Et beaucoup d'autres tables pour la finance, communication, examens, etc.

### 🚀 Déploiement

#### Configuration pour la Production

1. **Optimiser l'application**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

2. **Configurer le serveur web (Nginx)**
```nginx
server {
    listen 80;
    server_name edutnpro.tn;
    root /var/www/edutnpro-backend/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

3. **Configurer SSL avec Let's Encrypt**
```bash
sudo certbot --nginx -d edutnpro.tn -d www.edutnpro.tn
```

4. **Configurer les tâches planifiées (Cron)**
```bash
crontab -e
# Ajouter cette ligne :
* * * * * cd /var/www/edutnpro-backend && php artisan schedule:run >> /dev/null 2>&1
```

5. **Configurer la queue**
```bash
# Utiliser Supervisor pour gérer les workers
sudo apt-get install supervisor
sudo nano /etc/supervisor/conf.d/edutnpro-worker.conf
```

### 🧪 Tests

```bash
# Exécuter tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

### 📚 API Documentation

L'API REST est documentée avec Swagger/OpenAPI et accessible sur `/api/documentation` une fois l'application déployée.

#### Endpoints Principaux

- `POST /api/auth/login` - Connexion
- `POST /api/auth/register` - Inscription
- `GET /api/schools` - Liste des établissements
- `GET /api/students` - Liste des élèves
- `GET /api/grades` - Notes
- `POST /api/grades` - Ajouter une note
- etc.

### 🔧 Configuration

#### Rôles par Défaut

Le système inclut les rôles suivants :
- **Super Admin** - Accès complet
- **Admin** - Gestion de l'établissement
- **Directeur** - Direction pédagogique
- **Enseignant** - Gestion des cours et notes
- **Parent** - Consultation des informations enfants
- **Élève** - Consultation de ses informations

#### Permissions

Les permissions sont gérées de manière granulaire avec Spatie Permission :
- `view-students`, `create-students`, `edit-students`, `delete-students`
- `view-grades`, `create-grades`, `edit-grades`, `delete-grades`
- `view-teachers`, `create-teachers`, `edit-teachers`, `delete-teachers`
- etc.

### 📱 Applications Mobiles

Ce backend supporte 4 applications mobiles Flutter :
1. **EDUTN PRO Parent** - Pour les parents
2. **EDUTN PRO Enseignant** - Pour les enseignants
3. **EDUTN PRO Élève** - Pour les élèves
4. **EDUTN PRO Admin** - Pour les administrateurs

### 🌐 Internationalisation

Le système supporte plusieurs langues :
- **Arabe** (ar) - avec support RTL
- **Français** (fr) - langue par défaut
- **Anglais** (en)

Configuration dans `.env` :
```env
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
```

### 🔒 Sécurité

#### Checklist de Sécurité

- ✅ SSL/TLS activé (HTTPS)
- ✅ Mots de passe cryptés (Bcrypt)
- ✅ Protection CSRF
- ✅ Protection XSS
- ✅ Rate limiting sur les API
- ✅ Validation des entrées
- ✅ Authentification à deux facteurs (2FA) disponible
- ✅ Logs d'audit
- ✅ Sauvegardes automatiques

### 🤝 Contribution

Pour contribuer au projet :

1. Fork le repository
2. Créer une branche de feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

### 📄 Licence

© 2025 EDUTN PRO - Tous droits réservés

### 📞 Support

- **Email:** support@edutnpro.tn
- **Documentation:** docs.edutnpro.tn
- **Site web:** www.edutnpro.tn

---

**Développé avec ❤️ pour l'éducation tunisienne**
