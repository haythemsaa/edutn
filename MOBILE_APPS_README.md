# 📱 EDUTN PRO - Applications Mobiles Flutter

Suite complète de 4 applications mobiles Flutter pour EDUTN PRO.

## 🎯 Applications

### 1. 👨‍👩‍👧 EDUTN Parent (`edutn-parent/`)
**Pour les parents d'élèves**

✅ Suivi de tous mes enfants
✅ Notes, bulletins, moyennes
✅ Présences et absences
✅ Chat avec enseignants/administration
✅ Événements scolaires
✅ Paiements en ligne
✅ Documents officiels
✅ Suivi GPS du bus
✅ Menus cantine
✅ Demandes de rendez-vous

### 2. 👨‍🎓 EDUTN Student (`edutn-student/`)
**Pour les élèves**

✅ Mes notes et bulletins
✅ Mon emploi du temps
✅ Mes devoirs à faire
✅ Soumission de devoirs
✅ Cahier de texte numérique
✅ Gamification (badges, points)
✅ Bibliothèque numérique
✅ Chat avec enseignants
✅ Calendrier des examens
✅ Mes achievements

### 3. 👨‍🏫 EDUTN Teacher (`edutn-teacher/`)
**Pour les enseignants**

✅ Gestion de mes classes
✅ Saisie de notes rapide
✅ Appel/Présences (QR code)
✅ Cahier de texte digital
✅ Création de devoirs
✅ Correction en ligne
✅ Mon emploi du temps
✅ Chat avec parents/élèves
✅ Classes en ligne
✅ Analytics et rapports
✅ Gestion discipline
✅ Documents RH (congés, paie)

### 4. 💼 EDUTN Admin (`edutn-admin/`)
**Pour l'administration**

✅ Dashboard global
✅ Gestion multi-écoles
✅ Base élèves complète
✅ Gestion enseignants
✅ RH (contrats, congés, paie)
✅ Analytics avancées + IA
✅ Facturation et paiements
✅ Bibliothèque
✅ Transport (GPS fleet)
✅ Cantine
✅ Messagerie centralisée
✅ Génération de documents
✅ Backup/Restore
✅ Rapports Excel/PDF

## 🌍 Localisation

Toutes les applications supportent:
- **Français** (par défaut)
- **Arabe** avec support RTL complet
- Changement de langue à la volée

## 🏗️ Architecture

### Structure des projets
```
edutn-{role}/
├── lib/
│   ├── core/
│   │   ├── api/          # Laravel Sanctum API Client
│   │   ├── auth/         # Authentication BLoC
│   │   ├── localization/ # i18n (FR/AR)
│   │   ├── theme/        # Material Theme 3
│   │   └── utils/        # Helpers
│   ├── features/         # Feature modules
│   │   └── {feature}/
│   │       ├── data/
│   │       ├── domain/
│   │       └── presentation/
│   └── shared/
│       ├── widgets/      # Reusable widgets
│       └── models/       # Shared models
├── assets/
│   ├── images/
│   ├── icons/
│   └── translations/
│       ├── fr.json
│       └── ar.json
└── pubspec.yaml
```

### State Management
- **Flutter BLoC** pour la logique métier
- **Provider** pour injection de dépendances

### API Communication
- Laravel Sanctum authentication
- HTTP REST API
- Socket.IO pour temps réel
- Offline caching avec Hive

## 🔐 Sécurité

- ✅ Authentification Laravel Sanctum
- ✅ Tokens sécurisés (Flutter Secure Storage)
- ✅ SSL/TLS enforced
- ✅ Session management
- ✅ Auto-logout inactivité

## 🎨 Design System

- **Material Design 3**
- Thèmes clair/sombre
- Couleurs EDUTN PRO
- Typographie Cairo (support Arabe)
- Icônes personnalisées
- Animations fluides

## 📦 Dépendances Principales

### Core
- `flutter_bloc` - State management
- `go_router` - Navigation
- `easy_localization` - i18n
- `hive` - Local storage
- `dio` / `http` - HTTP client

### UI
- `cached_network_image` - Images
- `flutter_svg` - SVG support
- `shimmer` - Loading states
- `pull_to_refresh` - Refresh

### Features
- `socket_io_client` - Real-time chat
- `firebase_messaging` - Push notifications
- `fl_chart` - Charts & graphs
- `table_calendar` - Calendars
- `qr_flutter` / `qr_code_scanner` - QR codes
- `pdf` / `printing` - PDF generation
- `file_picker` / `image_picker` - Files
- `excel` - Excel export

## 🚀 Installation & Build

### Prerequisites
```bash
# Flutter SDK >= 3.0.0
flutter --version

# Check devices
flutter devices
```

### Installation
```bash
# Pour chaque application
cd edutn-{role}

# Installer dépendances
flutter pub get

# Vérifier
flutter doctor
```

### Development
```bash
# Lancer en mode debug
flutter run

# Hot reload automatique
r # dans le terminal
```

### Build Production

#### Android
```bash
# Build APK
flutter build apk --release

# Build App Bundle (Play Store)
flutter build appbundle --release
```

#### iOS
```bash
# Build iOS
flutter build ios --release

# Archive pour App Store
flutter build ipa --release
```

## ⚙️ Configuration

### Backend URL
Modifier dans chaque `lib/core/api/api_client.dart`:
```dart
static const String baseUrl = 'https://votre-backend.com/api';
```

### Firebase (Notifications)
1. Ajouter `google-services.json` (Android)
2. Ajouter `GoogleService-Info.plist` (iOS)

### App Icons
Placer icons dans `assets/images/app_icon.png` puis:
```bash
flutter pub run flutter_launcher_icons:main
```

## 📱 Plateformes Supportées

| Platform | Parent | Student | Teacher | Admin |
|----------|--------|---------|---------|-------|
| Android  | ✅ 5.0+ | ✅ 5.0+ | ✅ 5.0+ | ✅ 6.0+ |
| iOS      | ✅ 12.0+ | ✅ 12.0+ | ✅ 12.0+ | ✅ 13.0+ |
| Tablet   | ✅     | ✅      | ✅      | ✅ Optimized |
| Web      | 🚧     | 🚧      | 🚧      | 🚧     |

## 🔔 Notifications Push

- Firebase Cloud Messaging (FCM)
- Notifications locales
- Groupement par type
- Badges
- Sons personnalisés
- Deep linking

## 📊 Analytics & Monitoring

- Firebase Analytics
- Crashlytics
- Performance monitoring
- User engagement tracking

## 🧪 Tests

```bash
# Unit tests
flutter test

# Integration tests
flutter test integration_test/

# Code coverage
flutter test --coverage
```

## 📝 Code Quality

```bash
# Analyze
flutter analyze

# Format
flutter format .

# Linting
flutter pub run dart_code_metrics:metrics analyze lib
```

## 🌐 API Endpoints

Chaque application communique avec le backend Laravel via:

### Common
- `POST /api/login`
- `POST /api/logout`
- `GET /api/profile`

### Parent
- `GET /api/parent/children`
- `GET /api/parent/students/{id}/grades`
- `GET /api/parent/conversations`

### Student
- `GET /api/student/grades`
- `GET /api/student/assignments`
- `GET /api/student/timetable`

### Teacher
- `GET /api/teacher/classes`
- `POST /api/teacher/grades`
- `POST /api/teacher/attendance`

### Admin
- `GET /api/admin/dashboard`
- `GET /api/admin/schools`
- `GET /api/admin/analytics`

Voir `api_client.dart` dans chaque app pour la liste complète.

## 📖 Documentation

- [Parent App](/edutn-parent/README.md)
- [Student App](/edutn-student/README.md)
- [Teacher App](/edutn-teacher/README.md)
- [Admin App](/edutn-admin/README.md)

## 🤝 Support

Pour toute question ou problème:
- Email: support@edutnpro.tn
- Documentation: https://docs.edutnpro.tn

## 📄 Licence

© 2025 EDUTN PRO. Tous droits réservés.

---

## 🎯 Statistiques Projet

- **4 applications** mobiles complètes
- **Support bilingue** FR/AR avec RTL
- **50+ écrans** par application
- **200+ API endpoints** intégrés
- **Material Design 3**
- **Offline-first** architecture
- **Real-time** messaging
- **Push notifications**
- **QR code** scanning
- **GPS tracking**
- **PDF generation**
- **Excel export**
- **Charts & analytics**
- **Gamification**

**EDUTN PRO Mobile = L'écosystème éducatif mobile le plus complet de Tunisie! 🇹🇳📱**
