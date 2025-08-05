# HomeHub Real-time Server

Serveur Node.js WebSocket pour connecter HomeHub aux événements temps réel de Tuya Message Service.

## 🚀 Quick Start

### 1. Configuration
```bash
# Copier les credentials Tuya dans .env
cp .env.example .env
# Éditer .env avec tes vraies credentials
```

### 2. Installation
```bash
npm install
```

### 3. Démarrage
```bash
# Mode production
npm start

# Mode développement (auto-restart)
npm run dev
```

## ⚙️ Configuration (.env)

```env
# Credentials Tuya (mêmes que Laravel)
TUYA_CLIENT_ID=your_client_id_here
TUYA_CLIENT_SECRET=your_client_secret_here
TUYA_REGION=EU

# WebSocket local
WS_HOST=localhost
WS_PORT=3001

# Debug
DEBUG=true
```

## 🏗️ Architecture

```
Tuya Cloud Message Service (Pulsar)
         ↓ WebSocket SSL
    Node.js Server (tuya-client.js)
         ↓ WebSocket local
    Dashboard HomeHub (JavaScript)
```

## 📡 Messages WebSocket

### Du serveur vers le client (dashboard)

**Connexion établie:**
```json
{
  "type": "connection",
  "message": "Connected to HomeHub Real-time Server",
  "timestamp": 1640995200000
}
```

**Statut Tuya:**
```json
{
  "type": "tuya-status", 
  "connected": true,
  "timestamp": 1640995200000
}
```

**Événement device:**
```json
{
  "type": "device-event",
  "deviceId": "bf123456789abcdef",
  "eventType": "data-report",
  "timestamp": 1640995200000,
  "data": {
    "va_temperature": 235,
    "va_humidity": 65
  }
}
```

### Du client vers le serveur

**Ping:**
```json
{
  "type": "ping"
}
```

**Abonnement (optionnel):**
```json
{
  "type": "subscribe",
  "events": ["temperature", "camera", "switch"]
}
```

## 🔧 Fichiers

- `server.js` - Point d'entrée principal
- `config.js` - Configuration et URLs par région
- `tuya-client.js` - Client Tuya Message Service
- `websocket-server.js` - Serveur WebSocket local
- `.env` - Variables d'environnement

## 🎯 Types d'événements Tuya

- `data-report` - Nouvelles données (température, humidité)
- `online` - Device connecté
- `offline` - Device déconnecté
- `device-bind` - Device ajouté
- `device-unbind` - Device supprimé

## 🐛 Debug

Activer `DEBUG=true` dans .env pour voir tous les messages:

```bash
npm run dev
```

## 🔌 Intégration Dashboard

Remplacer le polling dans `public/js/dashboard/core/polling.js`:

```javascript
// Connexion WebSocket
const ws = new WebSocket('ws://localhost:3001');

ws.onmessage = function(event) {
  const message = JSON.parse(event.data);
  
  if (message.type === 'device-event') {
    // Traiter l'événement temps réel
    handleRealTimeDeviceEvent(message);
  }
};
```

## ⚡ Avantages vs Polling

- ✅ **Temps réel** : < 1 seconde vs 10 minutes
- ✅ **0 quota API** : Plus de requêtes REST répétitives  
- ✅ **Batterie** : Moins de consommation sur mobile
- ✅ **UX** : Interface vraiment reactive

## 🛠️ Production

1. Utiliser PM2 pour la gestion de processus
2. Configurer un reverse proxy (nginx)
3. Activer les logs rotatifs
4. Monitoring des connexions