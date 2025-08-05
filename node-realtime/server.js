#!/usr/bin/env node

import config from './config.js';
import TuyaOfficialClient from './tuya-client-official.js';
import WebSocketServer from './websocket-server.js';

console.log('🏠 HomeHub Real-time Server Starting...');
console.log('=====================================');

// Vérifier la configuration
if (!config.tuya.accessId || !config.tuya.accessKey) {
  console.error('❌ Error: TUYA_CLIENT_ID and TUYA_CLIENT_SECRET must be set in .env file');
  process.exit(1);
}

console.log(`📍 Tuya Region: ${config.tuya.region}`);
console.log(`🔧 Environment: ${config.tuya.env}`);
console.log(`🐛 Debug Mode: ${config.debug ? 'ON' : 'OFF'}`);
console.log(`🔐 WebSocket Auth: ${config.auth.enabled ? 'ENABLED' : 'DISABLED'}`);
if (config.auth.enabled) {
  console.log(`🔑 Laravel API: ${config.auth.laravel.host}${config.auth.laravel.validateUrl}`);
}
console.log('=====================================');

// Créer le serveur WebSocket local
const wsServer = new WebSocketServer({
  ...config.websocket,
  auth: config.auth
});

// Créer le client Tuya Message Service avec SDK officiel
const tuyaClient = new TuyaOfficialClient(config.tuya);

// Événements Tuya → WebSocket local
tuyaClient.on('authenticated', () => {
  console.log('🎉 Tuya authentication successful - ready to receive events!');
});

tuyaClient.on('device-event', async (deviceEvent) => {
  try {
    // Relayer l'événement vers les clients connectés (peut inclure traitement PHP)
    const clientsNotified = await wsServer.broadcastDeviceEvent(deviceEvent);

    if (clientsNotified === 0) {
      console.log('⚠️  No clients connected to receive the event');
    }
  } catch (error) {
    console.error('❌ Error broadcasting device event:', error.message);
  }
});

tuyaClient.on('auth-error', (error) => {
  console.error('❌ Tuya authentication failed:', error);
  console.error('   Check your TUYA_CLIENT_ID and TUYA_CLIENT_SECRET in .env');
});

tuyaClient.on('max-reconnect-reached', () => {
  console.error('❌ Max reconnection attempts reached for Tuya Message Service');
  console.error('   Shutting down server...');
  shutdown();
});

// Événements WebSocket Server
wsServer.on('client-connected', (ws, clientInfo) => {
  // Envoyer le statut de connexion Tuya au nouveau client
  wsServer.sendToClient(ws, {
    type: 'tuya-status',
    connected: tuyaClient.connected,
    timestamp: Date.now()
  });
});

// Démarrage des services
async function start() {
  try {
    // 1. Démarrer le serveur WebSocket local
    await wsServer.start();

    // 2. Se connecter à Tuya Message Service
    setTimeout(() => {
      tuyaClient.connect();
    }, 1000);

    console.log('');
    console.log('🎯 HomeHub Real-time Server is ready!');
    console.log(`   📡 Local WebSocket: ws://${config.websocket.host}:${config.websocket.port}`);
    console.log(`   🔗 Tuya Message Service: ${config.tuya.url}`);
    console.log('');
    console.log('💡 Next steps:');
    console.log('   1. Update your .env file with real Tuya credentials');
    console.log('   2. Connect your dashboard to ws://localhost:3001');
    console.log('   3. Watch real-time events flow!');
    console.log('');

  } catch (error) {
    console.error('❌ Failed to start server:', error.message);
    process.exit(1);
  }
}

// Gestion propre de l'arrêt
function shutdown() {
  console.log('');
  console.log('🔌 Shutting down HomeHub Real-time Server...');

  tuyaClient.disconnect();
  wsServer.stop();

  setTimeout(() => {
    console.log('👋 Goodbye!');
    process.exit(0);
  }, 1000);
}

// Gestion des signaux de fermeture
process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);

// Gestion des erreurs non capturées
process.on('uncaughtException', (error) => {
  console.error('❌ Uncaught Exception:', error);
  shutdown();
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
  shutdown();
});

// Démarrer le serveur
start();
