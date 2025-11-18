# EDUTN Parent - Application Mobile Parents

Application mobile Flutter pour les parents d'élèves de EDUTN PRO.

## 📱 Fonctionnalités

### 👶 Gestion des enfants
- Liste de tous mes enfants
- Profil détaillé de chaque enfant
- Sélection rapide d'enfant

### 📊 Suivi académique
- **Notes et moyennes** par matière
- **Bulletins trimestriels** (3 trimestres)
- **Rang dans la classe** et statistiques
- **Devoirs et assignments** avec statuts
- **Emploi du temps** de la semaine

### 📅 Présences
- Historique de présences/absences
- Justification d'absences
- Statistiques mensuelles
- Notifications d'absences en temps réel

### 💬 Messagerie
- Chat direct avec l'administration
- Chat avec les enseignants
- Messages de groupe
- Pièces jointes (PDF, images)
- Accusés de lecture
- Notifications push

### 📅 Événements
- Calendrier des événements scolaires
- Événements académiques, sportifs, culturels
- Inscription aux événements
- Notifications de rappel

### 💰 Paiements
- Liste des factures (payées/en attente)
- Paiement en ligne sécurisé
- Historique des paiements
- Reçus PDF téléchargeables

### 📄 Documents & Certificats
- Téléchargement de documents officiels
- Demande de certificats (scolarité, etc.)
- Bulletins PDF
- Attestations diverses

### 🚌 Transport scolaire
- Informations sur le bus assigné
- Suivi GPS en temps réel
- Itinéraire et arrêts
- Notifications arrivée/départ

### 🍽️ Cantine
- Menus de la semaine
- Réservations de repas
- Allergies et restrictions alimentaires
- Historique des repas

### 📋 Discipline
- Incidents disciplinaires
- Sanctions appliquées
- Commentaires de conduite
- Historique de comportement

### 📞 Rendez-vous
- Demande de rendez-vous avec enseignants
- Rendez-vous avec l'administration
- Calendrier des rendez-vous
- Visioconférence intégrée

## 🌍 Localisation

- **Français** (par défaut)
- **Arabe** avec support RTL complet
- Changement de langue dans l'application

## 🔐 Sécurité

- Authentification Laravel Sanctum
- Stockage sécurisé des tokens
- Session automatique
- Déconnexion automatique après inactivité

## 🎨 Design

- Material Design 3
- Mode clair/sombre
- Interface responsive
- Support tablette
- Animations fluides

## 📦 Installation

```bash
# Installer les dépendances
flutter pub get

# Lancer l'application
flutter run

# Build APK
flutter build apk --release

# Build iOS
flutter build ios --release
```

## 🔧 Configuration

Modifier l'URL du backend dans `lib/core/api/api_client.dart`:

```dart
static const String baseUrl = 'https://votre-backend.com/api';
```

## 📱 Compatibilité

- **Android**: 5.0 (API 21) et supérieur
- **iOS**: 12.0 et supérieur
- **Tablettes**: Support complet

## 🏗️ Architecture

```
lib/
├── core/
│   ├── api/           # API Client
│   ├── auth/          # Authentication BLoC
│   ├── localization/  # i18n
│   ├── theme/         # Thèmes
│   └── utils/         # Utilitaires
├── features/
│   ├── home/          # Écran d'accueil
│   ├── profile/       # Profil
│   ├── children/      # Gestion enfants
│   ├── grades/        # Notes et bulletins
│   ├── attendance/    # Présences
│   ├── messaging/     # Chat
│   ├── events/        # Événements
│   └── payments/      # Paiements
└── shared/
    ├── widgets/       # Widgets réutilisables
    └── models/        # Modèles de données
```

## 🔔 Notifications Push

- Firebase Cloud Messaging
- Notifications locales
- Groupement par type
- Sons personnalisés

## 📊 Analytics

- Suivi des événements
- Rapports d'utilisation
- Crash reporting

## 📖 API Endpoints Utilisés

Voir `lib/core/api/api_client.dart` pour la liste complète des endpoints.

## 👨‍💻 Développement

```bash
# Tests
flutter test

# Analyse de code
flutter analyze

# Format de code
flutter format .
```

## 📄 Licence

© 2025 EDUTN PRO. Tous droits réservés.
