# 🧩 Guide de Création de Widgets

Ce guide explique comment créer un nouveau widget pour HomeHub.

## 🏗️ Architecture d'un Widget

Chaque widget suit une structure standardisée qui respecte la séparation des responsabilités :

```
app/Widgets/MyFirstWidget/
├── MyFirstWidgetWidget.php   # Core du widget
├── config.json               # Configuration & métadonnées
├── app/
│   ├── Http/Controllers/     # API
│   └── Services/             # Métier
├── resources/
│   ├── js/                   # JavaScript du widget
│   ├── css/                  # Styles spécifiques
│   └── views/                # Templates Blade
├── routes/                   # Routes du widget
├── tests/                    # Tests
└── node-plugin/              # Middleware temps réel
    ├── config.json
    └── middleware.js
```

## 1️⃣ Configuration - `config.json`

Le fichier `config.json` définit les métadonnées et la configuration du widget :

```json
{
    "name": "myfirstwidget",
    "title": "My First Widget", 
    "icon": "👋",
    "enabled": true,
    "description": "Mon premier widget homeHub",
    "version": "1.0.0",
    "author": "Jean Michel Dev",
    "assets": {
        "js": ["resources/js/my-first-widget.js"],
        "css": ["resources/css/my-first-widget.css"]
    }
}
```

### Propriétés requises :
- **`name`** : Identifiant technique unique (snake_case)
- **`title`** : Nom affiché à l'utilisateur
- **`icon`** : Emoji
- **`enabled`** : Active/désactive le widget
- **`assets`** : Fichiers JS/CSS à charger

## 2️⃣ Core du Widget - `MyFirstWidgetWidget.php`

La classe principale implémente `WidgetInterface` et orchestre tout le widget :

```php
<?php

namespace App\Widgets\MyFirstWidget;

use App\Widgets\Core\WidgetInterface;
use App\Widgets\MyFirstWidget\App\Services\MyFirstWidgetService;

class HelloWorldWidget implements WidgetInterface
{
    private MyFirstWidgetService $myFirstWidgetService;
    private array $config;

    public function __construct(MyFirstWidgetService $myFirstWidgetService)
    {
        $this->myFirstWidgetService = $myFirstWidgetService;
        // Chargement automatique de la configuration
        $this->config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
    }

    // Métadonnées du widget
    public function getName(): string { return $this->config['name']; }
    public function getTitle(): string { return $this->config['title']; }
    public function getIcon(): string { return $this->config['icon']; }
    public function getAuthor(): string { return $this->config['author']; }
    public function getAssets(): array { return $this->config['assets']; }
    public function getConfig(): array { return $this->config; }

    /**
     * Données du widget
     * Récupère les données via le service et prépare la configuration
     */
    public function getData(): array
    {
        return [
            'devices' => $this->myFirstWidgetService->getDevices(),
            'config' => $this->getConfig()
        ];
    }

    /**
     * Rendu du widget - Génère le HTML final
     * Combine données + template Blade
     */
    public function render(): string
    {
        $data = $this->getData();
        return view('widgets::MyFirstWidget.resources.views.my-first-widget-widget', $data)->render();
    }
}
```

### Points clés :
- **`getData()`** : Récupère toutes les données nécessaires au rendu
- **`render()`** : Génère le HTML en combinant données + template
- **Service injection** : La logique métier est déléguée aux services

## 3️⃣ Controllers

```php
<?php

namespace App\Widgets\MyFirstWidget\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Widgets\MyFirstWidget\App\Services\MyFirstWidgetService;
use Illuminate\Http\JsonResponse;

class MyFirstWidgetController extends Controller
{
    private MyFirstWidgetService $myFirstWidgetService;

    /**
     * API endpoint - Récupérer les devices
     * GET /api/widgets/myFirstWidget/devices
     */
    public function getDevices(): JsonResponse
    {
        $devices = $this->myFirstWidgetService->getDevices();
        
        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }
}
```

### Rôles des Controllers :
- ✅ **API REST** : Endpoints
- ❌ **Rendu de vues** : C'est le rôle du `Widget::render()`

## 4️⃣ Middleware Temps Réel ([📖 Architecture détaillée](REALTIME_ARCHITECTURE.md))

Pour les widgets qui ont besoin du temps réel, créez un middleware Node.js :

### Configuration - `node-plugin/config.json`
```json
{
  "name": "myFirstWidget",
  "enabled": true,
  "priority": 100,  // Plus haut = traité en premier (widgets fréquents = priorité haute)
  "conditions": {
    "data_fields": ["field_1", "field_2"]
  },
  "description": "Process my first widget"
}
```

### Priority :
La **priority** détermine l'ordre d'exécution des middlewares (plus bas = en premier). Les middlewares sont testés séquentiellement jusqu'à ce qu'un `canProcess()` retourne `true`. **Optimisation** : placez les widgets qui reçoivent beaucoup de messages avec une priorité haute (ex: widget_1 = 99), les widgets occasionnels avec une priorité base (ex: widget_2 = 1).

### Middleware - `node-plugin/middleware.js`
```javascript
class MyFirstWidgetMiddleware {
  constructor(config) {
    this.config = config;
    this.name = 'myFirstWidget';
    this.deviceIds = []; // Injecté par le frontend lors de l'enregistrement du widget (📖 window.Widget)
  }

  setDeviceIds(deviceIds) {
    this.deviceIds = Array.isArray(deviceIds) ? deviceIds : [];
  }

  /**
   * Filtrage - Ce middleware peut-il traiter ce message ?
   */
  canProcess(deviceId, data) {
    // 1. Ne traiter QUE les devices concernés de ce widget
    if (this.deviceIds.length === 0 || !this.deviceIds.includes(deviceId)) {
      return false;
    }

    // 2. Vérifier le message tuya porte les données attendues selon config.json
    const hasExpectedData = data && (
      data.field_1 !== undefined || 
      data.field_2 !== undefined
    );

    return hasExpectedData;
  }

  /**
   * Traitement - Transformer les données pour le frontend
   */
  async process(deviceId, rawData) {
    // 1. Traitement métier
    const enrichedData = await this.callPhpApi(deviceId, rawData);
    
    // 2. Retour formaté pour le WebSocket
    return {
      eventType: 'myFirstWidget-update', // Convention nom du widget-action
      data: enrichedData // Données prêtes pour le frontend
    };
  }

  /**
   * Appel API pour enrichissement des données
   */
  async callPhpApi(deviceId, rawData) {
    const baseUrl = process.env.APP_URL';
    const response = await axios.post(`${baseUrl}/api/widgets/thermometers/process`, {
      device_id: deviceId,
      ...rawData
    });
    
    return response.data;
  }
}
```

### Méthodes requises pour le middleware :
- **`canProcess(deviceId, data)`** : Filtre les messages pertinents
- **`process(deviceId, rawData)`** : Traite et enrichit les données

### Frontend - Intégration du state manager `window.Widget`

Chaque widget frontend doit s'enregistrer via l'API `window.Widget` pour bénéficier du temps réel et d'un gestionnaire d'état :

```javascript
// 1. Instancier le widget avec son nom (doit matcher config.json)
var widget = new window.Widget('myFirstWidget', {
    enableRealtime: true,    // Active le temps réel (par défaut)
    realtimeEvents: [        // Événements écoutés (optionnel, convention automatique)
        'myFirstWidget-update',
        'myFirstWidget-online', 
        'myFirstWidget-offline'
    ]
});

// 2. Initialiser le state manager
widget.initState({
    'bf123abc': { name: 'Device 1', online: true },
    'cd456def': { name: 'Device 2', online: false }
});

// 3. Écouter les changements d'état
widget.onStateChange(function(deviceId, newState) {
    // Mettre à jour le DOM quand un l'etat d'un device change
    updateDeviceInDOM(deviceId, newState);
});
```

### API `window.Widget`

- **`initState(object)`** : Définit les device IDs + state initial → déclenche l'enregistrement WebSocket
- **`onStateChange(callback)`** : Fallback au changements d'état (WebSocket / modifications locales)
- **`updateDeviceState(deviceId, newState)`** : Met à jour l'état d'un device
- **`getDeviceState(deviceId)`** : Récupère l'état actuel d'un device

Le widget s'auto-configure : dès l'appel à `initState()`, il enregistre ses device IDs au serveur Node.js.

## 🎯 Workflow Complet

### Rendu Initial (Chargement de page)
```
1. Dashboard → Widget::render()
2. Widget → Service::getData()
3. Service → Base de données/API
4. Widget → Template Blade
5. Template → HTML final
```

### Mise à jour Temps Réel (Optionnel)
```
1. Frontend → window.Widget::initState (envoie device IDs)
2. realtime.js → Middleware::setDeviceIds() (injection)
3. Tuya Cloud → Node.js Server (données temps réel)
4. Node.js → Middleware::canProcess() (filtrage par device ID)
5. Middleware → Traite les données reçu
6. Middleware → WebSocket (données qualifiés)
7. Frontend → Mise à jour DOM
```

## 📝 Checklist de Création

Créer un nouveau widget :

### ✅ Fichiers de base
- [ ] `config.json` avec métadonnées complètes
- [ ] `MonWidgetWidget.php` implémentant `WidgetInterface`
- [ ] Service pour la logique métier
- [ ] Template Blade pour le rendu
- [ ] Controller pour les APIs uniquement

### ✅ Intégration
- [ ] Routes dans `routes/web.php` (si nécessaire)
- [ ] Assets JS/CSS chargés via `config.json`
- [ ] Tests unitaires et d'intégration

### ✅ Temps réel (optionnel)
- [ ] Middleware Node.js avec `canProcess()` et `process()`
- [ ] Configuration `node-plugin/config.json`

Cette architecture garantit des widgets **modulaires**, **testables** et **maintenables** ! 🚀
