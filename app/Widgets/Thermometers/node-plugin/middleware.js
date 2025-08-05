import axios from 'axios';

class ThermometersMiddleware {
  constructor(config) {
    this.config = config;
    this.name = 'thermometers';
    this.deviceIds = []; // Liste des device IDs gérés par ce middleware
  }

  /**
   * Configurer la liste des device IDs à gérer
   */
  setDeviceIds(deviceIds) {
    this.deviceIds = Array.isArray(deviceIds) ? deviceIds : [];
  }

  /**
   * Vérifier si les données correspondent à ce middleware
   */
  canProcess(deviceId, data) {
    // 🔒 RENFORCEMENT: Toujours vérifier que le deviceId est dans notre liste
    // Ne traiter QUE les devices qui nous appartiennent, même pour online/offline
    if (this.deviceIds.length > 0 && !this.deviceIds.includes(deviceId)) {
      return false;
    }

    // Si deviceIds est vide (pas encore configuré), ne traiter aucun événement
    if (this.deviceIds.length === 0) {
      return false;
    }

    // 2. Vérifier les types de messages supportés SEULEMENT pour nos devices
    const hasTemperatureData = data && (data.temp_current !== undefined || data.humidity_value !== undefined);
    const isOnlineOfflineMessage = data && (data.bizCode === 'deviceOnline' || data.bizCode === 'deviceOffline');

    return hasTemperatureData || isOnlineOfflineMessage;
  }

  /**
   * Traiter les données de température ou les événements online/offline
   */
  async process(deviceId, rawData) {
    try {
      // Gérer les messages online/offline
      if (rawData.bizCode === 'deviceOnline' || rawData.bizCode === 'deviceOffline') {
        const isOnline = rawData.bizCode === 'deviceOnline';
        const eventType = isOnline ? 'thermometer-online' : 'thermometer-offline';

        return {
          eventType: eventType,
          data: { online: isOnline }
        };
      }

      // Gérer les données de température (logique existante)
      const processedData = await this.callPhpApi(deviceId, rawData);

      // Construire la réponse avec les données traitées PHP + batterie depuis rawData
      const data = {
        temperature: processedData.temperature,
        humidity: processedData.humidity,
        heat_index: processedData.heat_index,
        thermal_emoji: processedData.thermal_emoji,
        temperature_color: processedData.temperature_color,
        humidity_color: processedData.humidity_color
      };

      // Ajouter les données de batterie si présentes dans les données brutes
      if (rawData.battery_percentage !== undefined) {
        data.battery = rawData.battery_percentage;
      }

      // Retourner avec l'eventType pour que WebSocket-server n'ait pas à deviner
      return {
        eventType: 'thermometer-update',
        data: data
      };

    } catch (error) {
      console.error(`❌ [THERMOMETERS] ERROR:`, error.message);
      console.error(`❌ [THERMOMETERS] STACK:`, error.stack);
      console.error(`❌ [THERMOMETERS] FULL ERROR:`, error);
      throw error;
    }
  }

  /**
   * Appeler l'API PHP pour le traitement des températures
   */
  async callPhpApi(deviceId, rawData) {
    if (!process.env.APP_URL) {
      throw new Error('APP_URL environment variable is required but not set');
    }
    
    const baseUrl = process.env.APP_URL;
    const phpApiUrl = `${baseUrl}/api/widgets/thermometers/process`;

    // Configuration axios pour Node.js
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    const payload = {
      device_id: deviceId
    };

    if (rawData.temp_current !== undefined) {
      payload.temp_current = rawData.temp_current;
    }
    if (rawData.humidity_value !== undefined) {
      payload.humidity_value = rawData.humidity_value;
    }


    const response = await axios.post(phpApiUrl, payload, {
      timeout: 5000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    if (response.data.success) {
      return response.data;
    } else {
      throw new Error('PHP API returned success: false');
    }
  }
}

export default ThermometersMiddleware;
