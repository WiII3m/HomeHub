class CameraMiddleware {
    constructor() {
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
        const hasCameraData = data && data.basic_private !== undefined;
        
        // Chercher basic_private dans le tableau status
        const hasCameraDataInStatus = data && data.status && Array.isArray(data.status) && 
            data.status.some(status => status.basic_private !== undefined);

        const isOnlineOfflineMessage = data && (
            data.bizCode === 'deviceOnline' ||
            data.bizCode === 'deviceOffline'
        );

        return hasCameraData || hasCameraDataInStatus || isOnlineOfflineMessage;
    }

    async process(deviceId, rawData) {
        try {
            // Gérer les messages online/offline
            if (rawData.bizCode === 'deviceOnline' || rawData.bizCode === 'deviceOffline') {
                const isOnline = rawData.bizCode === 'deviceOnline';
                const eventType = isOnline ? 'camera-online' : 'camera-offline';

                return {
                    eventType: eventType,
                    data: { online: isOnline }
                };
            }

            // Gérer les données de caméra
            if (rawData.basic_private !== undefined) {
                return {
                    eventType: 'camera-update',
                    data: { basic_private: rawData.basic_private }
                };
            }

            // NOUVEAU: Chercher basic_private dans le tableau status
            if (rawData.status && Array.isArray(rawData.status)) {
                for (const status of rawData.status) {
                    if (status.basic_private !== undefined) {
                        return {
                            eventType: 'camera-update', 
                            data: { basic_private: status.basic_private }
                        };
                    }
                }
            }

            return null;

        } catch (error) {
            throw error;
        }
    }
}

export default CameraMiddleware;
