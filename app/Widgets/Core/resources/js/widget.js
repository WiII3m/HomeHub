window.Widget = (function() {
    'use strict';

    function Widget(widgetName, options) {
        this.widgetName = widgetName;
        this.state = {};
        this.subscribers = [];
        this.options = options || {};

        if (this.options.enableRealtime !== false) {
            this.initRealtime();
        }
    }

    Widget.prototype.initRealtime = function() {
        var self = this;

        if (!window.DashboardRealtime) {
            return;
        }

        var events = this.options.realtimeEvents || [
            this.widgetName + '-update',
            this.widgetName + '-online',
            this.widgetName + '-offline'
        ];

        PolyfillUtils.forEach(events, function(eventType) {
            window.DashboardRealtime.subscribe(eventType, function(event) {
                self.handleRealtimeEvent(eventType, event);
            });
        });

        this.registerDeviceIds();
    };

    Widget.prototype.handleRealtimeEvent = function(eventType, event) {
        var action = eventType.split('-')[1];

        switch(action) {
            case 'update':
                this.updateDeviceState(event.deviceId, event.data);
                break;
            case 'online':
                this.updateDeviceState(event.deviceId, { online: true });
                break;
            case 'offline':
                this.updateDeviceState(event.deviceId, { online: false });
                break;
        }
    };

    Widget.prototype.registerDeviceIds = function() {
        var self = this;

        if (!window.DashboardRealtime || !window.DashboardRealtime.isConnected()) {
            setTimeout(function() {
                self.registerDeviceIds();
            }, 1000);
            return;
        }

        var deviceIds = Object.keys(this.state);
        if (deviceIds.length > 0) {
            window.DashboardRealtime.send({
                type: 'configure-widget',
                widget: this.widgetName,
                deviceIds: deviceIds
            });
        }
    };

    Widget.prototype.updateDeviceState = function(deviceId, newState) {
        var currentState = this.state[deviceId] || {};

        this.state[deviceId] = PolyfillUtils.mergeObjects(currentState, newState);

        this.notifyStateChange(deviceId, this.state[deviceId]);
    };

    Widget.prototype.onStateChange = function(callback) {
        this.subscribers.push(callback);
    };

    Widget.prototype.notifyStateChange = function(deviceId, deviceState) {
        var self = this;
        this.subscribers.forEach(function(callback) {
            try {
                callback(deviceId, deviceState);
            } catch (error) {

            }
        });
    };

    Widget.prototype.initState = function(initialState) {
        this.state = initialState || {};
        this.registerDeviceIds();
    };

    Widget.prototype.getDeviceState = function(deviceId) {
        return this.state[deviceId] || {};
    };

    Widget.prototype.getDeviceIds = function() {
        return Object.keys(this.state);
    };

    return Widget;
})();
