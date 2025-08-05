window.DashboardThermometers = (function() {
    'use strict';

    var widget = null;
    var currentThermometerId = null;
    var sortableInstance = null;

    function createThermometerCard(thermometerId, data) {
        var name = data.name || 'Thermomètre';
        var online = data.online != null ? data.online : true;
        var temperature = data.temperature;
        var humidity = data.humidity;
        var heatIndex = data.heat_index;
        var battery = data.battery;
        var temperatureColor = data.temperature_color;
        var humidityColor = data.humidity_color;
        var thermalEmoji = data.thermal_emoji;

        return `
            <div class="col-sm-6 col-xs-12 margin-bottom-16">
                <div class="bg-gray-light border-radius-6 padding-16 cursor-pointer hover-gray-lighter active-gray-lightest transition-bg" data-thermometer-card data-device-id="${thermometerId}">

                    <div class="margin-bottom-16 line-height-tight display-flex justify-content-space-between align-items-center">
                        <span class="font-weight-500 text-gray-dark font-size-medium">${name}</span>
                        <div class="font-size-small text-gray-light">
                            <span class="status-point ${online ? 'online' : 'offline'} border-radius-50 margin-right-4 display-inline-block vertical-align-middle"></span>
                            <span class="vertical-align-middle">${online ? 'En ligne' : 'Hors ligne'}</span>
                        </div>
                    </div>

                    <div class="margin-bottom-12">
                        <!-- Température -->
                        <div class="data-row margin-bottom-8">
                            <span class="float-left font-size-medium text-gray">Température</span>
                            <span class="float-right temperature-value font-size-base font-weight-bold" style="color: ${temperatureColor};">
                                ${temperature !== null && temperature !== undefined ? temperature.toFixed(1) + '°C' : '--°C'}
                            </span>
                        </div>

                        <div class="data-row margin-bottom-8">
                            <span class="float-left font-size-medium text-gray">Humidité</span>
                            <span class="float-right humidity-value font-size-base font-weight-bold" style="color: ${humidityColor};">
                                ${humidity !== null && humidity !== undefined ? humidity + '%' : '--%'}
                            </span>
                        </div>
                    </div>

                    <div class="margin-bottom-12 display-flex justify-content-space-between align-items-center">
                        <span class="font-size-medium text-gray">Ressenti</span>
                        <div class="display-flex align-items-center">
                            <span class="thermal-emoji font-size-base margin-right-8">${thermalEmoji}</span>
                            <span class="heat-index-value font-size-base font-weight-bold" style="color: ${temperatureColor};">
                                ${heatIndex !== null && heatIndex !== undefined ? heatIndex.toFixed(1) + '°C' : '--°C'}
                            </span>
                        </div>
                    </div>

                    ${battery !== null && battery !== undefined ? `
                        <!-- Ligne de séparation -->
                        <hr class="border-0 border-top-1 border-gray margin-vertical-12">

                        <!-- Section batterie -->
                        <div class="display-flex font-size-small text-gray-light justify-content-space-between align-items-center">
                            <span>Batterie</span>
                            <span>${battery}%</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    function createHistoryLayout() {
        return `
            <div>
                <div class="margin-bottom-24">
                    <button class="bg-transparent border-0 text-primary cursor-pointer font-size-medium" data-back-button>← Retour à la liste</button>
                </div>
                <div id="temperature-content">
                    <!-- Contenu dynamique injecté ici -->
                </div>
            </div>
        `;
    }

    function createHistoryLoader() {
        return `
            <div class="text-center padding-vertical-32">
                <div class="margin-bottom-16">Chargement de l'historique...</div>
            </div>
        `;
    }

    function createHistoryError(message) {
        return `
            <div class="alert alert-danger margin-24">
                <strong>Erreur:</strong> ${message}
            </div>
        `;
    }

    function createHistoryChart(currentDays) {
        return `
            <div class="margin-bottom-32 text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm period-btn ${currentDays === 1 ? 'btn-primary' : 'btn-default'}" data-period-button data-period="1">24H</button>
                    <button type="button" class="btn btn-sm period-btn ${currentDays === 3 ? 'btn-primary' : 'btn-default'}" data-period-button data-period="3">3J</button>
                    <button type="button" class="btn btn-sm period-btn ${currentDays === 7 ? 'btn-primary' : 'btn-default'}" data-period-button data-period="7">7J</button>
                </div>
            </div>

            <div class="text-center margin-bottom-16">
                <h4 class="margin-0">Historique ${currentDays} jour${currentDays > 1 ? 's' : ''}</h4>
            </div>

            <!-- Canvas pour le graphique -->
            <div class="position-relative margin-bottom-16">
                <canvas id="temperature-chart" width="800" height="400" class="width-full border-1 border-gray-light border-radius-6"></canvas>
            </div>

            <div class="display-flex justify-content-center margin-bottom-32" style="flex-wrap: wrap; gap: 16px;">
                <div class="display-flex align-items-center">
                    <div style="width: 16px; height: 3px; background-color: #FF6B6B; margin-right: 6px;"></div>
                    <span style="font-size: 0.9em;">Température</span>
                </div>
                <div class="display-flex align-items-center">
                    <div style="width: 16px; height: 3px; background-color: #FF8E53; margin-right: 6px;"></div>
                    <span style="font-size: 0.9em;">Ressentie</span>
                </div>
                <div class="display-flex align-items-center">
                    <div style="width: 16px; height: 8px; background-color: #4ECDC4; margin-right: 6px;"></div>
                    <span style="font-size: 0.9em;">Humidité</span>
                </div>
            </div>

            <div id="temperature-statistics"></div>
        `;
    }

    function sortDeviceIds(deviceIds, thermometersOrder) {
        if (!thermometersOrder || Object.keys(thermometersOrder).length === 0) {
            return deviceIds;
        }

        return deviceIds.slice().sort(function(a, b) {
            var weightA = thermometersOrder[a] || 999;
            var weightB = thermometersOrder[b] || 999;
            return weightA - weightB;
        });
    }

    function renderThermometersCards() {
        var container = document.getElementById('thermometers-dynamic-container');
        if (!container || widget.getDeviceIds().length === 0) {
            return;
        }

        // Trier les device IDs selon les settings
        var deviceIds = widget.getDeviceIds();
        var settings = getSettings();
        var sortedDeviceIds = sortDeviceIds(deviceIds, settings.thermometersOrder);

        // Générer les cards triées
        var cards = '';
        PolyfillUtils.forEach(sortedDeviceIds, function(thermometerId) {
            var thermometerData = widget.getDeviceState(thermometerId);
            cards += createThermometerCard(thermometerId, thermometerData);
        });

        // Header + cards d'un coup
        container.innerHTML = `
            <div class="row">
                <div class="col-xs-8">
                    <h2 class="h4 margin-top-0">
                        <span class="font-size-large margin-right-8 vertical-align-middle">${window.thermometersWidgetConfig.icon}</span>
                        <span class="vertical-align-middle">${window.thermometersWidgetConfig.title}</span>
                    </h2>
                </div>
                <div class="col-xs-4 text-right">
                    <button class="btn btn-default btn-sm margin-top-4 border-0 font-size-large width-48 padding-0" data-settings-button>
                        <span class="font-size-base">⚙️</span>
                    </button>
                </div>
            </div>
            <div class="row margin-top-16" data-thermometers-list>
                ${cards}
            </div>
        `;

        currentThermometerId = null;

        setupEventDelegation();
    }

    function setupEventDelegation() {
        var container = document.getElementById('thermometers-dynamic-container');
        if (!container) return;

        // Event delegation pour tous les clics dans le widget thermometers
        container.addEventListener('click', function(e) {
            // Clic sur une carte de thermomètre
            var thermometerCard = e.target.closest('[data-thermometer-card]');
            if (thermometerCard) {
                var thermometerId = thermometerCard.getAttribute('data-device-id');
                var nameElement = thermometerCard.querySelector('.font-weight-500');
                var deviceName = nameElement ? nameElement.textContent : 'Thermomètre';
                showThermometerHistory(thermometerId, deviceName);
                return;
            }

            // Clic sur le bouton settings
            var settingsButton = e.target.closest('[data-settings-button]');
            if (settingsButton) {
                openSettingsModal();
                return;
            }

            // Clic sur le bouton retour
            var backButton = e.target.closest('[data-back-button]');
            if (backButton) {
                showThermometersCards();
                return;
            }

            // Clic sur un bouton de période
            var periodButton = e.target.closest('[data-period-button]');
            if (periodButton) {
                var period = parseInt(periodButton.getAttribute('data-period'));
                changeTemperaturePeriod(period);
                return;
            }
        });
    }

    function updateThermometerCard(thermometerId, thermometerState) {
        if (currentThermometerId === thermometerId) {
            updateThermometerHeaderStatus(thermometerId, thermometerState);
        }

        var updatedCard = createThermometerCard(thermometerId, thermometerState);

        var displayedCard = document.querySelector('[data-device-id="' + thermometerId + '"]');
        if (displayedCard) {
            var parentCol = displayedCard.closest('.col-sm-6');
            if (parentCol) {
                parentCol.outerHTML = updatedCard;
            }
        }
    }

    function updateThermometerHeaderStatus(thermometerId, thermometerState) {
        var statusContainer = document.getElementById('thermometer-header-status-' + thermometerId);
        if (!statusContainer || !thermometerState) return;

        var statusDot = document.getElementById('thermometer-header-dot-' + thermometerId);
        var statusText = document.getElementById('thermometer-header-text-' + thermometerId);

        if (statusDot && statusText) {
            var online = thermometerState.online != null ? thermometerState.online : true;
            var statusMessage = online ? 'En ligne' : 'Hors ligne';

            statusDot.className = statusDot.className.replace(/\b(online|offline)\b/g, '');
            statusDot.classList.add(online ? 'online' : 'offline');

            statusText.textContent = statusMessage;
        }
    }

    function showHistoryLoader() {
        var contentDiv = document.getElementById('temperature-content');
        if (contentDiv) {
            contentDiv.innerHTML = createHistoryLoader();
        }
    }

    function showHistoryError(message) {
        var contentDiv = document.getElementById('temperature-content');
        if (contentDiv) {
            contentDiv.innerHTML = createHistoryError(message);
        }
    }

    function showHistoryChart(data, currentDays) {
        var contentDiv = document.getElementById('temperature-content');
        if (!contentDiv) return;

        contentDiv.innerHTML = createHistoryChart(currentDays);

        var canvas = document.getElementById('temperature-chart');
        if (!canvas) {
            return;
        }

        // TODO: Implémenter drawTemperatureChart si nécessaire

        var statsContainer = document.getElementById('temperature-statistics');
        if (statsContainer) {
            // TODO: Implémenter displayTemperatureStatistics si nécessaire
            statsContainer.innerHTML = '<p class="text-muted">Statistiques à implémenter</p>';
        }

        // Les event listeners sont gérés par la delegation, plus besoin de les réattacher
    }

    function loadTemperatureHistory(thermometerId, deviceName, days) {
        days = days || 1;
        var container = document.getElementById('thermometers-dynamic-container');
        if (!container) return;

        var thermometerData = widget.getDeviceState(thermometerId);
        var online = thermometerData && thermometerData.online != null ? thermometerData.online : true;
        var statusText = online ? 'En ligne' : 'Hors ligne';

        var titleHtml = `
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="h4 margin-top-0 display-flex justify-content-space-between align-items-center width-full">
                        <div>
                            <span class="font-size-large margin-right-8">🌡️</span>
                            <span>${deviceName}</span>
                        </div>
                        <div id="thermometer-header-status-${thermometerId}" class="font-size-small text-gray-light" data-device-id="${thermometerId}">
                            <span id="thermometer-header-dot-${thermometerId}" class="status-point ${online ? 'online' : 'offline'} border-radius-50 margin-right-4 display-inline-block vertical-align-middle"></span>
                            <span id="thermometer-header-text-${thermometerId}" class="vertical-align-middle">${statusText}</span>
                        </div>
                    </h2>
                </div>
            </div>
        `;

        container.innerHTML = titleHtml + createHistoryLayout();

        showHistoryLoader();

        HttpClient.get('/widgets/thermometers/history?device_id=' + encodeURIComponent(thermometerId) + '&days=' + days)
            .then(function(data) {
                if (data.success) {
                    showHistoryChart(data.data, days);
                } else {
                    showHistoryError(data.error || 'Erreur de chargement des données');
                }
            })
            .catch(function(error) {
                showHistoryError('Erreur de connexion au serveur');
            });
    }

    function changeTemperaturePeriod(days) {
        if (currentThermometerId) {
            // On récupère le nom depuis les données du widget plutôt qu'un élément DOM inexistant
            var thermometerData = widget.getDeviceState(currentThermometerId);
            var deviceName = thermometerData.name || 'Thermomètre';
            loadTemperatureHistory(currentThermometerId, deviceName, days);
        }
    }

    function showThermometerHistory(thermometerId, deviceName) {
        currentThermometerId = thermometerId;
        loadTemperatureHistory(thermometerId, deviceName);
    }

    function showThermometersCards() {
        currentThermometerId = null;
        renderThermometersCards();
    }

    function init() {
        if (window.thermometersState) {
            widget = new Widget('thermometers', {
                realtimeEvents: ['thermometer-update', 'thermometer-online', 'thermometer-offline']
            });

            widget.initState(window.thermometersState);

            widget.onStateChange(function(thermometerId, thermometerState) {
                updateThermometerCard(thermometerId, thermometerState);
            });

            renderThermometersCards();
        }
    }

    function openSettingsModal() {
        window.DashboardModal.open('thermometersSettingsModal', function() {
            initializeSortable();
            loadSettingsIntoModal();
        });
    }

    function initializeSortable() {
        var sortableContainer = document.getElementById('sortable-thermometers');
        if (!sortableContainer || !window.Sortable) {
            return;
        }

        if (sortableInstance) {
            sortableInstance.destroy();
        }

        sortableInstance = window.Sortable.create(sortableContainer, {
            onEnd: function() {
                autoSaveSettings();
            }
        });
    }

    function loadSettingsIntoModal() {
        var settings = getSettings();

        if (settings.thermometersOrder && Object.keys(settings.thermometersOrder).length > 0) {
            applySavedOrderToModal(settings.thermometersOrder);
        }
    }

    function applySavedOrderToModal(thermometersOrder) {
        var container = document.getElementById('sortable-thermometers');
        if (!container) return;

        var thermometerElements = Array.prototype.slice.call(container.children);

        thermometerElements.sort(function(a, b) {
            var thermometerIdA = a.getAttribute('data-thermometer-id');
            var thermometerIdB = b.getAttribute('data-thermometer-id');

            var weightA = thermometersOrder[thermometerIdA] || 999;
            var weightB = thermometersOrder[thermometerIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < thermometerElements.length; i++) {
            container.appendChild(thermometerElements[i]);
        }
    }

    function autoSaveSettings() {
        var settings = {
            thermometersOrder: getThermometersOrderFromModal()
        };

        localStorage.setItem('thermometers-settings', JSON.stringify(settings));

        setTimeout(function() {
            applyThermometersOrder(settings.thermometersOrder);
        }, 100);
    }

    function getThermometersOrderFromModal() {
        var thermometersOrder = {};
        var sortableItems = document.querySelectorAll('#sortable-thermometers li');

        for (var i = 0; i < sortableItems.length; i++) {
            var thermometerId = sortableItems[i].getAttribute('data-thermometer-id');
            if (thermometerId) {
                thermometersOrder[thermometerId] = i + 1;
            }
        }

        return thermometersOrder;
    }

    function getSettings() {
        try {
            var settings = localStorage.getItem('thermometers-settings');
            return settings ? JSON.parse(settings) : {
                thermometersOrder: {}
            };
        } catch (e) {
            return {
                thermometersOrder: {}
            };
        }
    }

    function applyThermometersOrder(thermometersOrder) {
        if (!thermometersOrder || Object.keys(thermometersOrder).length === 0) {
            return;
        }

        var container = document.querySelector('[data-thermometers-list]');
        if (!container) {
            return;
        }

        var thermometerColumns = Array.prototype.slice.call(container.children);

        thermometerColumns.sort(function(a, b) {
            var thermometerIdA = a.querySelector('[data-thermometer-card]').getAttribute('data-device-id');
            var thermometerIdB = b.querySelector('[data-thermometer-card]').getAttribute('data-device-id');

            var weightA = thermometersOrder[thermometerIdA] || 999;
            var weightB = thermometersOrder[thermometerIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < thermometerColumns.length; i++) {
            container.appendChild(thermometerColumns[i]);
        }
    }

    return {
        init: init,
        showThermometersCards: showThermometersCards,
        renderThermometersCards: renderThermometersCards,
        showThermometerHistory: showThermometerHistory,
        loadTemperatureHistory: loadTemperatureHistory,
        changeTemperaturePeriod: changeTemperaturePeriod,
        openSettingsModal: openSettingsModal
    };
})();

window.showThermometerHistory = function(thermometerId, deviceName) {
    if (window.DashboardThermometers && window.DashboardThermometers.showThermometerHistory) {
        window.DashboardThermometers.showThermometerHistory(thermometerId, deviceName);
    }
};

window.changeTemperaturePeriod = function(days) {
    if (window.DashboardThermometers && window.DashboardThermometers.changeTemperaturePeriod) {
        window.DashboardThermometers.changeTemperaturePeriod(days);
    }
};

window.showThermometersCards = function() {
    if (window.DashboardThermometers && window.DashboardThermometers.showThermometersCards) {
        window.DashboardThermometers.showThermometersCards();
    }
};

window.openThermometersSettingsModal = function() {
    if (window.DashboardThermometers && window.DashboardThermometers.openSettingsModal) {
        window.DashboardThermometers.openSettingsModal();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardThermometers) {
        window.DashboardThermometers.init();
    }
});
