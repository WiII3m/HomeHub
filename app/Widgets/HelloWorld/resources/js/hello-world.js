/**
 * Widget HelloWorld - JavaScript Frontend
 * Version simplifiée avec DOM généré par Blade
 */

window.DashboardHelloWorld = (function() {
    'use strict';

    var sortableInstance = null;

    /**
     * Gestion du modal de settings
     * Ouvre le modal et prépare l'interface de configuration
     */
    function openSettingsModal() {
        window.DashboardModal.open('helloworldSettingsModal', function() {
            initializeSortable();
            loadSettingsIntoModal();
        });
    }

    /**
     * Initialise le drag & drop dans le modal de settings
     */
    function initializeSortable() {
        var sortableContainer = document.getElementById('sortable-devices');
        if (!sortableContainer || !window.Sortable) return;

        if (sortableInstance) {
            sortableInstance.destroy();
        }

        sortableInstance = window.Sortable.create(sortableContainer, {
            forceFallback: true,
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                autoSaveSettings();
            }
        });
    }

    /**
     * Charge les settings dans le modal
     */
    function loadSettingsIntoModal() {
        var settings = getSettings();
        
        var container = document.getElementById('sortable-devices');
        if (!container) return;

        var deviceCards = document.querySelectorAll('.device-card');
        var html = '';
        
        for (var i = 0; i < deviceCards.length; i++) {
            var card = deviceCards[i];
            var deviceId = card.getAttribute('data-device-id');
            var deviceName = card.querySelector('.font-weight-500').textContent;
            
            html += `
                <li data-device-id="${deviceId}" class="bg-gray-light border-1 border-gray border-radius-4 padding-12 margin-bottom-8 cursor-move">
                    <div class="row">
                        <div class="col-xs-1 text-center">
                            <span class="handle text-gray-light font-size-large line-height-none">⋮⋮</span>
                        </div>
                        <div class="col-xs-10">
                            <span class="font-weight-500">${deviceName}</span>
                        </div>
                    </div>
                </li>
            `;
        }
        
        container.innerHTML = html;

        if (settings.devicesOrder && Object.keys(settings.devicesOrder).length > 0) {
            applySavedOrderToModal(settings.devicesOrder);
        }
    }

    /**
     * Applique l'ordre sauvegardé au modal
     */
    function applySavedOrderToModal(devicesOrder) {
        var container = document.getElementById('sortable-devices');
        if (!container) return;

        var deviceElements = Array.prototype.slice.call(container.children);

        deviceElements.sort(function(a, b) {
            var deviceIdA = a.getAttribute('data-device-id');
            var deviceIdB = b.getAttribute('data-device-id');
            var weightA = devicesOrder[deviceIdA] || 999;
            var weightB = devicesOrder[deviceIdB] || 999;
            return weightA - weightB;
        });

        for (var i = 0; i < deviceElements.length; i++) {
            container.appendChild(deviceElements[i]);
        }
    }

    /**
     * Sauvegarde automatique des settings
     */
    function autoSaveSettings() {
        var settings = {
            devicesOrder: getDevicesOrderFromModal()
        };

        localStorage.setItem('helloworld-settings', JSON.stringify(settings));

        setTimeout(function() {
            applyDevicesOrder(settings.devicesOrder);
        }, 100);
    }

    /**
     * Extrait l'ordre des devices depuis le modal
     */
    function getDevicesOrderFromModal() {
        var devicesOrder = {};
        var sortableItems = document.querySelectorAll('#sortable-devices li');

        for (var i = 0; i < sortableItems.length; i++) {
            var deviceId = sortableItems[i].getAttribute('data-device-id');
            if (deviceId) {
                devicesOrder[deviceId] = i + 1;
            }
        }

        return devicesOrder;
    }

    /**
     * Récupère les settings depuis le localStorage
     */
    function getSettings() {
        try {
            var settings = localStorage.getItem('helloworld-settings');
            return settings ? JSON.parse(settings) : {
                devicesOrder: {}
            };
        } catch (e) {
            return {
                devicesOrder: {}
            };
        }
    }

    /**
     * Applique l'ordre des devices au DOM principal
     */
    function applyDevicesOrder(devicesOrder) {
        if (!devicesOrder || Object.keys(devicesOrder).length === 0) {
            return;
        }

        var container = document.querySelector('.devices-list');
        if (!container) {
            return;
        }

        var deviceColumns = Array.prototype.slice.call(container.children);

        deviceColumns.sort(function(a, b) {
            var deviceIdA = a.querySelector('.device-card').getAttribute('data-device-id');
            var deviceIdB = b.querySelector('.device-card').getAttribute('data-device-id');

            var weightA = devicesOrder[deviceIdA] || 999;
            var weightB = devicesOrder[deviceIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < deviceColumns.length; i++) {
            container.appendChild(deviceColumns[i]);
        }
    }

    /**
     * Initialisation du widget
     */
    function init() {
        // Appliquer l'ordre sauvegardé au chargement
        setTimeout(function() {
            var settings = getSettings();
            if (settings.devicesOrder && Object.keys(settings.devicesOrder).length > 0) {
                applyDevicesOrder(settings.devicesOrder);
            }
        }, 100);
    }

    return {
        init: init,
        openSettingsModal: openSettingsModal
    };
})();

/**
 * Fonction globale pour ouvrir le modal de settings
 */
window.openHelloworldSettingsModal = function() {
    if (window.DashboardHelloWorld && window.DashboardHelloWorld.openSettingsModal) {
        window.DashboardHelloWorld.openSettingsModal();
    }
};

/**
 * Initialisation automatique au chargement du DOM
 */
document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardHelloWorld) {
        window.DashboardHelloWorld.init();
    }
});