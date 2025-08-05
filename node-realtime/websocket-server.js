import WebSocket, { WebSocketServer as WSServer } from 'ws';
import { EventEmitter } from 'events';
import PluginManager from './core/plugin-manager.js';

class WebSocketServer extends EventEmitter {
  constructor(config) {
    super();
    this.config = config;
    this.wss = null;
    this.clients = new Set();
    this.pluginManager = new PluginManager();
  }

  async start() {
    console.log(`🚀 Starting WebSocket server on ws://${this.config.host}:${this.config.port}`);

    // Charger les plugins avant de démarrer le serveur WebSocket
    const pluginConfigPath = this.config.pluginConfigPath || '../storage/node-config.json';
    await this.pluginManager.loadFromConfig(pluginConfigPath);

    this.wss = new WSServer({
      host: this.config.host,
      port: this.config.port
    });

    this.wss.on('connection', async (ws, request) => {
      const clientInfo = {
        ip: request.socket.remoteAddress,
        userAgent: request.headers['user-agent'] || 'Unknown'
      };

      console.log(`✅ Dashboard connected from ${clientInfo.ip}`);
      
      this.clients.add(ws);

      // Envoyer un message de bienvenue
      this.sendToClient(ws, {
        type: 'connection',
        message: 'Connected to HomeHub Real-time Server',
        timestamp: Date.now()
      });

      // Gérer les messages du client
      ws.on('message', (data) => {
        try {
          const message = JSON.parse(data.toString());
          this.handleClientMessage(ws, message);
        } catch (error) {
          console.error('❌ Error parsing client message:', error.message);
        }
      });

      // Gérer la déconnexion
      ws.on('close', (code, reason) => {
        console.log(`❌ Dashboard disconnected: ${code} - ${reason}`);
        this.clients.delete(ws);
      });

      // Gérer les erreurs
      ws.on('error', (error) => {
        console.error('❌ Client WebSocket error:', error.message);
        this.clients.delete(ws);
      });

      this.emit('client-connected', ws, clientInfo);
    });

    this.wss.on('error', (error) => {
      console.error('❌ WebSocket server error:', error.message);
    });

    console.log(`✅ WebSocket server ready!`);
  }

  handleClientMessage(ws, message) {
    switch (message.type) {
      case 'ping':
        this.sendToClient(ws, { type: 'pong', timestamp: Date.now() });
        break;
      case 'subscribe':
        // Le client peut s'abonner à des types d'événements spécifiques
        this.handleSubscription(ws, message);
        break;
      case 'configure-widget':
        // Configuration des device IDs pour un widget spécifique
        this.handleWidgetConfiguration(ws, message);
        break;
      default:
        break;
    }
  }

  handleSubscription(ws, message) {
    // Gérer les abonnements (optionnel pour plus tard)
    ws.subscriptions = message.events || ['all'];

    this.sendToClient(ws, {
      type: 'subscription-confirmed',
      events: ws.subscriptions,
      timestamp: Date.now()
    });
  }

  handleWidgetConfiguration(ws, message) {
    const { widget, deviceIds } = message;

    if (!widget || !Array.isArray(deviceIds)) {
      console.error('❌ Invalid widget configuration message:', message);
      return;
    }


    // Trouver le middleware correspondant dans le plugin manager
    const middleware = this.pluginManager.getMiddlewareByName(widget);
    if (middleware && typeof middleware.setDeviceIds === 'function') {
      middleware.setDeviceIds(deviceIds);

      this.sendToClient(ws, {
        type: 'widget-configured',
        widget: widget,
        deviceIds: deviceIds,
        success: true,
        timestamp: Date.now()
      });
    } else {
      console.error(`❌ Middleware not found or doesn't support device ID configuration: ${widget}`);

      this.sendToClient(ws, {
        type: 'widget-configured',
        widget: widget,
        success: false,
        error: 'Middleware not found or incompatible',
        timestamp: Date.now()
      });
    }
  }

  sendToClient(ws, data) {
    if (ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify(data));
    }
  }

  broadcast(data) {
    const message = JSON.stringify(data);
    let sentCount = 0;
    
    this.clients.forEach(ws => {
      if (ws.readyState === WebSocket.OPEN) {
        ws.send(message);
        sentCount++;
      } else {
        this.clients.delete(ws);
      }
    });

    return sentCount;
  }

  async broadcastDeviceEvent(deviceEvent) {
    const result = await this.pluginManager.processData(deviceEvent.deviceId, deviceEvent.data);

    if (result.processed && result.eventType) {
      const message = {
        type: result.eventType,
        deviceId: deviceEvent.deviceId,
        timestamp: deviceEvent.timestamp,
        data: result.data
      };

      const sentCount = this.broadcast(message);
      console.log('📡 Sent to', sentCount, 'clients:', result.eventType);
      return sentCount;
    } else {
      return this.broadcastRawEvent(deviceEvent);
    }
  }

  broadcastRawEvent(deviceEvent) {
    const message = {
      type: deviceEvent.type,
      deviceId: deviceEvent.deviceId,
      timestamp: deviceEvent.timestamp,
      data: deviceEvent.data
    };

    return this.broadcast(message);
  }


  getStats() {
    return {
      connectedClients: this.clients.size,
      serverRunning: this.wss ? true : false,
      plugins: this.pluginManager.getStats()
    };
  }

  stop() {
    console.log('🔌 Stopping WebSocket server...');

    if (this.wss) {
      this.wss.close(() => {
        console.log('✅ WebSocket server stopped');
      });
    }
  }
}

export default WebSocketServer;
