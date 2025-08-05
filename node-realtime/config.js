import dotenv from 'dotenv';
import path from 'path';

// Charger le .env depuis la racine du projet Laravel (un niveau au-dessus)
dotenv.config({ path: path.resolve('../.env') });

const config = {
  tuya: {
    accessId: process.env.TUYA_CLIENT_ID,
    accessKey: process.env.TUYA_CLIENT_SECRET,
    region: process.env.TUYA_REGION || 'EU',
    env: 'PROD'
  },

  websocket: {
    host: process.env.WS_HOST || 'localhost',
    port: parseInt(process.env.WS_PORT) || 3001,
    pluginConfigPath: process.env.PLUGIN_CONFIG_PATH || '../storage/node-config.json'
  },

  laravel: {
    baseUrl: process.env.APP_URL
  },

  auth: {
    enabled: false
  },

  debug: process.env.DEBUG === 'true'
};

// URLs WebSocket officielles par région (Message Service Pulsar)
const regionUrls = {
  CN: 'wss://mqe.tuyacn.com:8285/',
  US: 'wss://mqe.tuyaus.com:8285/',
  EU: 'wss://mqe.tuyaeu.com:8285/',
  IN: 'wss://mqe.tuyain.com:8285/'
};

config.tuya.url = regionUrls[config.tuya.region] || regionUrls.EU;

export default config;
