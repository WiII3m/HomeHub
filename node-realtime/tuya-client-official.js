import { createRequire } from 'module';
import { EventEmitter } from 'events';

const require = createRequire(import.meta.url);
const TuyaWebsocket = require('./tuya-pulsar-ws-node/dist').default;

class TuyaOfficialClient extends EventEmitter {
  constructor(config) {
    super();
    this.config = config;
    this.client = null;
    this.connected = false;
    this.debug = config.debug || false;
  }

  connect() {
    if (this.connected) {
      console.log('⚠️  Already connected to Tuya Message Service');
      return;
    }

    console.log(`🔌 Connecting to Tuya Message Service (${this.config.region})...`);
    
    // Mapping des régions vers les constantes du SDK
    const regionMapping = {
      CN: TuyaWebsocket.URL.CN,
      US: TuyaWebsocket.URL.US,
      EU: TuyaWebsocket.URL.EU,
      IN: TuyaWebsocket.URL.IN
    };

    // Créer le client avec le SDK officiel
    this.client = new TuyaWebsocket({
      accessId: this.config.accessId,
      accessKey: this.config.accessKey,
      url: regionMapping[this.config.region] || TuyaWebsocket.URL.EU,
      env: this.config.env === 'TEST' ? TuyaWebsocket.env.TEST : TuyaWebsocket.env.PROD,
      maxRetryTimes: 50
    });

    this.setupEventHandlers();
    
    try {
      this.client.start();
      console.log('🚀 SDK officiel démarré...');
    } catch (error) {
      console.error('❌ Erreur lors du démarrage SDK:', error);
      this.emit('error', error);
    }
  }

  setupEventHandlers() {
    // Connexion ouverte
    this.client.open(() => {
      console.log('✅ Connected to Tuya Message Service (Official SDK)');
      this.connected = true;
      this.emit('authenticated');
    });

    // Message reçu
    this.client.message((ws, message) => {
      if (this.debug) {
        console.log('📨 Raw Tuya message:', JSON.stringify(message, null, 2));
      }

      // Acquitter le message
      this.client.ackMessage(message.messageId);

      // Traiter le message
      this.handleTuyaMessage(message);
    });

    // Reconnexion
    this.client.reconnect(() => {
      console.log('🔄 Reconnecting to Tuya Message Service...');
      this.connected = false;
    });

    // Ping/Pong
    this.client.ping(() => {
      if (this.debug) {
        console.log('🏓 Ping sent to Tuya');
      }
    });

    this.client.pong(() => {
      if (this.debug) {
        console.log('🏓 Pong received from Tuya');
      }
    });

    // Connexion fermée
    this.client.close((ws, ...args) => {
      console.log('🔌 Connection to Tuya closed:', ...args);
      this.connected = false;
    });

    // Erreur
    this.client.error((ws, error) => {
      console.error('❌ Tuya SDK error:', error);
      this.emit('auth-error', error);
    });
  }

  handleTuyaMessage(message) {
    console.log('🔍 [TUYA-CLIENT] MESSAGE RECEIVED - bizCode:', message?.payload?.data?.bizCode);
    
    try {
      if (this.debug) {
        console.log('📨 Raw message structure:', JSON.stringify(message, null, 2));
      }

      // Parser la vraie structure Tuya
      const payload = message.payload;
      if (!payload || !payload.data) {
        console.log('⚠️ No payload.data in message');
        return;
      }

      const bizData = payload.data.bizData;
      if (!bizData) {
        console.log('⚠️ No bizData in message');
        return;
      }

      const deviceId = bizData.devId;
      const properties = bizData.properties || [];
      const bizCode = payload.data.bizCode;
      
      console.log('🎯 Device event received:');
      console.log(`   Device: ${deviceId}`);
      console.log(`   BizCode: ${bizCode}`);
      
      // Gérer les différents types d'événements
      let data = {};
      let eventType;
      
      if (bizCode === 'deviceOnline') {
        console.log('   Status: 🟢 ONLINE');
        data = { bizCode: 'deviceOnline', online: true };
        eventType = 'online';
      } else if (bizCode === 'deviceOffline') {
        console.log('   Status: 🔴 OFFLINE');
        data = { bizCode: 'deviceOffline', online: false };
        eventType = 'offline';
      } else if (bizCode === 'devicePropertyMessage') {
        console.log(`   Properties: ${properties.length} changes`);
        if (properties.length > 0) {
          properties.forEach((prop, index) => {
            console.log(`   [${index + 1}] ${prop.code}: ${prop.value}`);
            
            // Log spécial pour basic_private
            if (prop.code === 'basic_private') {
              console.log(`   📹 CAMERA PRIVACY MODE detected: ${prop.value ? 'ON' : 'OFF'}`);
            }
          });
        }
        data = this.convertTuyaPropertiesToData(properties);
        
        // Détection spéciale pour basic_private
        const hasBasicPrivate = properties.some(p => p.code === 'basic_private');
        if (hasBasicPrivate) {
          console.log(`   🎯 basic_private event - data will be:`, data);
        }

        
        eventType = 'property-change';
      } else {
        console.log(`   Unknown bizCode: ${bizCode}`);
        data = bizData;
        eventType = bizCode;
      }

      // Émettre l'événement pour le serveur WebSocket
      this.emit('device-event', {
        deviceId: deviceId,
        type: eventType,
        timestamp: payload.data.ts || Date.now(),
        data: data
      });

    } catch (error) {
      console.error('❌ Error processing Tuya message:', error);
      if (this.debug) {
        console.error('❌ Full message:', JSON.stringify(message, null, 2));
      }
    }
  }

  convertTuyaPropertiesToData(properties) {
    // Convertir le format properties de Tuya vers notre format simple
    const data = {};
    
    if (Array.isArray(properties)) {
      properties.forEach(prop => {
        data[prop.code] = prop.value;
        
        // Log spécial pour la batterie
        if (prop.code === 'battery_percentage') {
          console.log(`🔋 BATTERY LEVEL detected: ${prop.value}%`);
        }
      });
    }
    
    return data;
  }

  disconnect() {
    console.log('🔌 Disconnecting from Tuya Message Service...');
    this.connected = false;
    
    if (this.client) {
      // Le SDK officiel n'a pas de méthode close explicite
      // Il se ferme automatiquement
      this.client = null;
    }
  }

  isConnected() {
    return this.connected;
  }
}

export default TuyaOfficialClient;