window.SettingsModal = (function() {
    'use strict';

    var widgetOrderSortable = null;

    function openSettingsModal() {
        window.DashboardModal.open('settingsModal', function() {
            applyOrderToModalList();
            initializeWidgetSortable();
            loadWidgetStatesInModal();
        });
    }

    function closeSettingsModal() {
        window.DashboardModal.close('settingsModal');
    }

    function refreshDashboard() {
        window.location.reload();
    }

    function applyOrderToModalList() {
        var settings = getWidgetSettings();
        if (!settings || Object.keys(settings).length === 0) {
            return;
        }

        var container = document.getElementById('sortable-widgets');
        if (!container) return;

        var items = Array.prototype.slice.call(container.children);

        items.sort(function(a, b) {
            var idA = a.getAttribute('data-widget-id');
            var idB = b.getAttribute('data-widget-id');
            var weightA = (settings[idA] && settings[idA].position) || 999;
            var weightB = (settings[idB] && settings[idB].position) || 999;
            return weightA - weightB;
        });

        for (var i = 0; i < items.length; i++) {
            container.appendChild(items[i]);
        }
    }

    function loadWidgetStatesInModal() {
        var settings = getWidgetSettings();

        var switches = document.querySelectorAll('[id^="widget-switch-"]');
        for (var i = 0; i < switches.length; i++) {
            var checkbox = switches[i];
            var widgetName = checkbox.id.replace('widget-switch-', '');
            var widgetId = 'widget-' + widgetName;
            var widgetSetting = settings[widgetId];

            var isEnabled = !widgetSetting || widgetSetting.enabled !== false;

            checkbox.checked = isEnabled;
        }
    }

    function initializeWidgetSortable() {
        var el = document.getElementById('sortable-widgets');
        if (el && window.Sortable) {
            try {
                // Détruire l'instance existante s'il y en a une
                if (widgetOrderSortable) {
                    widgetOrderSortable.destroy();
                    widgetOrderSortable = null;
                }

                widgetOrderSortable = window.Sortable.create(el, {
                    onEnd: function() {
                        autoSaveWidgetSettings();
                    }
                });
            } catch(e) {

            }
        }
    }

    function autoSaveWidgetSettings() {
        var settings = getWidgetSettingsFromModal();

        localStorage.setItem('widget-settings', JSON.stringify(settings));

        setTimeout(function() {
            if (window.DashboardLayout) {
                window.DashboardLayout.applyWidgetOrder(settings);
            }
        }, 100);
    }

    function getWidgetSettingsFromModal() {
        var settings = getWidgetSettings();
        var sortableItems = document.querySelectorAll('#sortable-widgets li');

        if (Object.keys(settings).length === 0) {
            for (var i = 0; i < sortableItems.length; i++) {
                var widgetId = sortableItems[i].getAttribute('data-widget-id');
                if (widgetId) {
                    settings[widgetId] = { position: i + 1, enabled: true };
                }
            }
        } else {

            for (var i = 0; i < sortableItems.length; i++) {
                var widgetId = sortableItems[i].getAttribute('data-widget-id');
                if (widgetId) {
                    if (!settings[widgetId]) {
                        settings[widgetId] = { enabled: true };
                    }
                    settings[widgetId].position = i + 1;
                }
            }
        }

        return settings;
    }

    function getWidgetSettings() {
        try {
            var settings = localStorage.getItem('widget-settings');
            return settings ? JSON.parse(settings) : {};
        } catch (e) {
            return {};
        }
    }

    function init() {

    }

    return {
        init: init,
        openSettingsModal: openSettingsModal,
        closeSettingsModal: closeSettingsModal,
        refreshDashboard: refreshDashboard,
        getWidgetSettings: getWidgetSettings
    };
})();

window.openSettingsModal = function() {
    if (window.SettingsModal) {
        window.SettingsModal.openSettingsModal();
    }
};

window.refreshDashboard = function() {
    if (window.SettingsModal) {
        window.SettingsModal.refreshDashboard();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.SettingsModal) {
        window.SettingsModal.init();
    }
});

if (document.readyState !== 'loading') {
    if (window.SettingsModal) {
        window.SettingsModal.init();
    }
}
