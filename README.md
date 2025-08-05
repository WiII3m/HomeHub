# 🏠 HomeHub β (Tuya Cloud Dashboard)

HomeHub est un dashboard pour la gestion et le monitoring de vos appareils connectés Tuya Cloud. Ce dashboard est optimisé pour fonctionner sur d'anciens devices ( iOS 9 | Android 6 ).

Le projet repose sur une **architecture modulaire avancée** conçue pour la scalabilité et la maintenabilité. Cette approche garantit une séparation claire des responsabilités et un cloisonnement strict des logiques métiers.

## 🧰 Stack technique

- **Backend** : Laravel 12, PHP 8.2+
- **Frontend** : Vanilla JS, Bootstrap 3
- **Temps réel** : Node.js, WebSocket ([📖 Architecture détaillée](realtime_architecture.md))
- **API** : Tuya Cloud IoT Platform

## ⚙️ Architecture modulaire

Chaque widget du dashboard suit une architecture en couches stricte :

```
Widget/
├── WidgetClass.php         # Interface et configuration
├── config.json             # Configuration du widget
├── app/
│   ├── Http/Controllers/   # Logique de présentation
│   ├── Services/           # Logique métier isolée
│   ├── Models/             # Accès aux données (optionnel)
│   └── Console/Commands/   # Commandes Artisan spécifiques
├── resources/
│   ├── js/                 # JavaScript spécifique au widget
│   ├── css/                # Styles encapsulés
│   └── views/              # Templates Blade dédiés
├── routes/                 # Routes isolées du widget
├── tests/                  # Tests du widget
│   ├── Feature/            # Tests d'intégration PHP (Controllers, Views)
│   ├── js/                 # Tests fonctionnels JavaScript (Widgets)
│   └── Unit/               # Tests unitaires PHP (Services)
├── public/                 # Assets statiques (images, etc.)
└── node-plugin/            # Middleware Node.js pour WebSocket
    ├── config.json         # Configuration du plugin
    └── middleware.js       # Logique temps réel
```

### Avantages de cette approche

- **Modularité** : Système extensible pour prendre en charge de nouveaux types d'appareils.
- **Évolutivité** : Ajout/suppression/désactivation de widgets sans impact sur l'application.
- **Maintenabilité** : Logique métier compartimentée et testable indépendamment.

➡️ **[📖 Guide de création de widgets](widget_creation.md)**

## 📐 Tests

Le projet utilise une stratégie de tests à 3 niveaux pour assurer la qualité du code :

### Structure des tests
```
app/Widgets/MonWidget/
├── tests/
│   ├── Unit/           # Tests unitaires PHP (Services)
│   ├── Feature/        # Tests d'intégration PHP (Controllers, Views)
│   └── js/             # Tests fonctionnels JavaScript (Widgets)
```
Cette approche garantit la couverture de la stack technique, de la logique métier backend jusqu'aux interactions utilisateur frontend.

### PHPUnit (Backend)
```bash
# Tous les tests PHP
php artisan test

# Tests spécifiques
php artisan test app/Widgets/Cameras/tests
```

**Types de tests PHP :**
- **Tests unitaires** : Services et logique métier (`app/Services/`)
- **Tests d'intégration** : Contrôleurs et rendu des vues Laravel (`app/Http/Controllers/`)

### Vitest (Frontend) 
```bash
# Tous les tests JavaScript
npm run test:js

# Mode UI
npm run test:js:ui
```

**Types de tests JavaScript :**
- **Tests fonctionnels** : Widgets complets avec DOM et interactions utilisateur
- **Tests d'intégration** : Rendu HTML, event listeners, appels API mockés

## 📦 Installation

### Prérequis

- PHP 8.2+
- Composer
- Node.js 16+
- Compte développeur Tuya Cloud

Clonez le projet et installez les dépendances :

```bash
git clone <repository-url>
cd homehub
composer install
npm install
npm run build
cd node-realtime
npm install
php artisan generate:node-config
```

Copiez et configurez l'environnement :

```bash
cp .env.example .env
php artisan key:generate
cd node-realtime
cp .env.example .env

```

Variables d'environnement Laravel (`.env`)

```env
# Application
APP_NAME=HomeHub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Base de données (SQLite)
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# Session et cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Tuya Cloud API
TUYA_CLIENT_ID=your_client_id
TUYA_CLIENT_SECRET=your_access_secret
TUYA_HOME_ID=your_home_id
TUYA_ENDPOINT=https://openapi.tuyaeu.com
```

Variables d'environnement Node.js (`node-realtime/.env`)

```env
# Configuration Tuya (mêmes identifiants que Laravel)
TUYA_CLIENT_ID=your_client_id_here
TUYA_CLIENT_SECRET=your_client_secret_here
TUYA_REGION=EU

# Configuration WebSocket
WS_HOST=localhost
WS_PORT=3001

# Laravel Base URL (pour les appels API des middlewares)
APP_URL=http://localhost:8000

# Debug
DEBUG=true
```

Lancez les migrations :

```bash
php artisan migrate
```

## 🖥️ Utilisation

Démarrez le serveur de développement :

```bash
php artisan serve
```

Pour le temps réel, démarrez le serveur WebSocket :

```bash
cd node-realtime
npm start
```

## 📋 Commandes du projet

```bash
# Régénérer la config Node.js (après ajout/modification de widgets)
php artisan generate:node-config

# Synchroniser les thermomètres (à exécuter toutes les heures via cron)
php artisan thermometers:sync

# Serveur de développement
php artisan serve

# Build des assets
npm run build
npm run dev
```

### Configuration des cron

Pour maintenir l'historique des thermomètres à jour, ajoutez cette tâche cron :

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne (remplacer le chemin par le vôtre)
0 * * * * cd /path/to/homehub && php artisan thermometers:sync >> /dev/null 2>&1
```

## Contribution

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push sur la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créez une Pull Request

## Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.
