
(function() {
    'use strict';

    var DashboardRealtime = {
        ws: null,
        connected: false,
        reconnectAttempts: 0,
        maxReconnectAttempts: 10,
        reconnectDelay: 3000,
        subscribers: {},
        config: {
            url: 'ws://localhost:3001',
            debug: false
        },

        init: function() {

            this.connect();
        },

        connect: function() {
            if (this.connected) return;

            try {
                this.ws = new WebSocket(this.config.url);
                this.setupEventHandlers();
            } catch (error) {
                this.scheduleReconnect();
            }
        },

        setupEventHandlers: function() {
            var self = this;

            this.ws.onopen = function() {

                self.connected = true;
                self.reconnectAttempts = 0;
                self.send({ type: 'ping' });
                self.notifySubscribers('connection', { connected: true });

                if (window.DashboardUtils) {
                    window.DashboardUtils.showStatusMessage('Temps réel activé', 'success');
                }
            };

            this.ws.onmessage = function(event) {
                try {
                    var message = JSON.parse(event.data);
                    if (message.type !== 'pong') {

                    }
                    self.handleMessage(message);
                } catch (error) {

                }
            };

            this.ws.onclose = function(event) {

                self.connected = false;
                self.notifySubscribers('connection', { connected: false });
                if (window.DashboardUtils) {
                    window.DashboardUtils.showStatusMessage('Connexion temps réel fermée', 'warning');
                }
                self.scheduleReconnect();
            };

            this.ws.onerror = function(error) {

            };
        },

        handleMessage: function(message) {
            switch (message.type) {
                case 'connection':
                    break;
                case 'tuya-status':
                    this.handleTuyaStatus(message);
                    break;
                case 'pong':
                    break;

                default:
                    if (message.deviceId) {
                        this.handleDeviceEvent(message);
                    }
            }
        },

        handleTuyaStatus: function(message) {
            if (message.connected) {
                if (window.DashboardUtils) {
                    window.DashboardUtils.showStatusMessage('Tuya temps réel connecté', 'success');
                }
            } else {
                if (window.DashboardUtils) {
                    window.DashboardUtils.showStatusMessage('Tuya temps réel déconnecté', 'warning');
                }
            }
        },

        handleDeviceEvent: function(message) {
            this.notifySubscribers(message.type, message);
        },

        subscribe: function(eventType, callback) {
            if (!this.subscribers[eventType]) {
                this.subscribers[eventType] = [];
            }
            this.subscribers[eventType].push(callback);
        },

        unsubscribe: function(eventType, callback) {
            if (this.subscribers[eventType]) {
                var index = this.subscribers[eventType].indexOf(callback);
                if (index > -1) {
                    this.subscribers[eventType].splice(index, 1);
                }
            }
        },

        notifySubscribers: function(eventType, data) {
            if (this.subscribers[eventType]) {
                this.subscribers[eventType].forEach(function(callback, index) {
                    try {
                        callback(data);
                    } catch (error) {

                    }
                });
            } else {

            }
        },

        send: function(data) {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify(data));
                return true;
            } else {
                if (this.config.debug) {

                }
                return false;
            }
        },

        scheduleReconnect: function() {
            var self = this;

            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                if (window.DashboardUtils) {
                    window.DashboardUtils.showStatusMessage('Impossible de se reconnecter au temps réel', 'danger');
                }
                return;
            }

            this.reconnectAttempts++;
            setTimeout(function() {
                self.connect();
            }, this.reconnectDelay);
        },

        disconnect: function() {
            this.connected = false;

            if (this.ws) {
                this.ws.close();
                this.ws = null;
            }
        },

        isConnected: function() {
            return this.connected && this.ws && this.ws.readyState === WebSocket.OPEN;
        },

        enableDebug: function() {
            this.config.debug = true;
        },

        getStats: function() {
            return {
                connected: this.connected,
                reconnectAttempts: this.reconnectAttempts,
                subscribersCount: Object.keys(this.subscribers).length
            };
        },

        sendMessage: function(type, data) {
            return this.send({
                type: type,
                data: data
            });
        }
    };

    window.DashboardRealtime = DashboardRealtime;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            DashboardRealtime.init();
        });
    } else {
        DashboardRealtime.init();
    }

})();
