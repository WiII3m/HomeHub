class LightsMiddleware {
  constructor(config) {
    this.config = config;
    this.name = 'lights';
    this.deviceIds = [];
  }

  setDeviceIds(deviceIds) {
    this.deviceIds = Array.isArray(deviceIds) ? deviceIds : [];
  }

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
    const hasLightData = data && data.switch_led !== undefined;
    const isOnlineOfflineMessage = data && (data.bizCode === 'deviceOnline' || data.bizCode === 'deviceOffline');

    return hasLightData || isOnlineOfflineMessage;
  }


  async process(deviceId, rawData) {
    try {
      if (rawData.bizCode === 'deviceOnline' || rawData.bizCode === 'deviceOffline') {
        const isOnline = rawData.bizCode === 'deviceOnline';
        const eventType = isOnline ? 'light-online' : 'light-offline';

        return {
          eventType: eventType,
          data: { online: isOnline }
        };
      }

      if (rawData.switch_led !== undefined) {
        return {
          eventType: 'light-update',
          data: { switch_state: rawData.switch_led }
        };
      }

      return null;

    } catch (error) {
      throw error;
    }
  }
}

export default LightsMiddleware;
