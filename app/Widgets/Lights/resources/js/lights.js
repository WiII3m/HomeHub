
window.DashboardLights = (function() {
    'use strict';

    var widget = null;
    var isToggling = {};
    var sortableInstance = null;
    var originalRoomsData = [];

    function createLightCard(light, roomId) {
        var name = light.name || 'Lumière';
        var online = light.online !== undefined ? light.online : false;
        var switchState = light.switch_state !== undefined ? light.switch_state : false;

        return `
            <div class="col-sm-6 col-xs-12 margin-bottom-16">
                <div class="light-item bg-gray-light border-radius-6 padding-16 height-full cursor-pointer transition-bg hover-gray-lighter active-gray-lightest${!online ? ' opacity-06' : ''}"
                     data-light-card data-device-id="${light.id}"
                     data-room-id="${roomId}"
                     onclick="toggleLightFromCard('${light.id}')">

                    <div class="row">
                        <!-- Informations de la lumière -->
                        <div class="col-xs-8">
                            <div class="margin-bottom-4">
                                <span class="font-weight-500 text-gray-dark font-size-medium light-name-ellipsis">${name}</span>
                            </div>
                            <div class="font-size-small text-gray-light">
                                <div id="light-status-${light.id}" class="status-point ${online ? 'online' : 'offline'} border-radius-50 margin-right-8 display-inline-block vertical-align-middle"></div>
                                <span id="light-status-text-${light.id}" class="vertical-align-middle">${online ? 'En ligne' : 'Hors ligne'}</span>
                            </div>
                        </div>

                        <!-- Switch de contrôle -->
                        <div class="col-xs-4 text-right">
                            <div class="light-control" onclick="event.stopPropagation()">
                                <label class="switch">
                                    <input type="checkbox"
                                           id="light-switch-${light.id}"
                                           ${switchState ? 'checked' : ''}
                                           ${!online ? 'disabled' : ''}
                                           onchange="toggleLight('${light.id}', this.checked)"
                                           class="opacity-0">
                                    <span class="slider ${switchState ? 'on' : 'off'}">
                                        <span class="slider-handle"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function createRoomSection(room) {
        var roomName = room.room_name || 'Pièce';
        var lights = room.lights || [];

        var lightsHtml = '';
        PolyfillUtils.forEach(lights, function(light) {
            lightsHtml += createLightCard(light, room.room_id);
        });

        return `
            <div class="room-section margin-bottom-32" data-room-section data-room-id="${room.room_id}">
                <div class="row">
                    <div class="col-xs-12">
                        <h3 class="h5 margin-bottom-16 text-gray-dark font-weight-500 border-bottom-1 border-gray padding-bottom-4">
                            ${roomName}
                        </h3>
                    </div>
                </div>
                <div class="room-lights row" data-room-lights>
                    ${lightsHtml}
                </div>
            </div>
        `;
    }

    function findLightInRooms(deviceId) {
        for (var roomIndex = 0; roomIndex < originalRoomsData.length; roomIndex++) {
            var room = originalRoomsData[roomIndex];
            if (room.lights && Array.isArray(room.lights)) {
                for (var lightIndex = 0; lightIndex < room.lights.length; lightIndex++) {
                    if (room.lights[lightIndex].id === deviceId) {
                        return {
                            light: room.lights[lightIndex],
                            roomIndex: roomIndex,
                            lightIndex: lightIndex
                        };
                    }
                }
            }
        }
        return null;
    }

    function updateLightInRooms(deviceId, newState) {
        var lightInfo = findLightInRooms(deviceId);
        if (!lightInfo) {
            return null;
        }

        originalRoomsData[lightInfo.roomIndex].lights[lightInfo.lightIndex] =
            PolyfillUtils.mergeObjects(lightInfo.light, newState);

        return originalRoomsData[lightInfo.roomIndex].lights[lightInfo.lightIndex];
    }

    function renderLightsView() {
        var container = document.getElementById('lights-dynamic-container');
        if (!container) {
            return;
        }

        var sectionsHtml = '';
        PolyfillUtils.forEach(originalRoomsData, function(room) {
            sectionsHtml += createRoomSection(room);
        });

        container.innerHTML = `
            <div id="lights-list-view" class="margin-top-16">
                ${sectionsHtml}
            </div>
        `;
    }

    function updateSwitchOnly(deviceId, switchState) {

        var switchElement = document.getElementById('light-switch-' + deviceId);
        var sliderElement = switchElement ? switchElement.nextElementSibling : null;

        if (switchElement && sliderElement) {

            switchElement.checked = switchState;

            sliderElement.className = sliderElement.className.replace(/\b(on|off)\b/g, '');
            sliderElement.classList.add(switchState ? 'on' : 'off');

        } else {
        }
    }

    function updateOnlineStatus(deviceId, online) {
        var statusElement = document.getElementById('light-status-' + deviceId);
        var statusTextElement = document.getElementById('light-status-text-' + deviceId);
        var switchElement = document.getElementById('light-switch-' + deviceId);

        if (statusElement) {
            statusElement.className = statusElement.className.replace(/\b(online|offline)\b/g, '');
            statusElement.classList.add(online ? 'online' : 'offline');
        }

        if (statusTextElement) {
            statusTextElement.textContent = online ? 'En ligne' : 'Hors ligne';
        }

        if (switchElement) {
            switchElement.disabled = !online;
        }

        var lightItem = document.querySelector('[data-device-id="' + deviceId + '"]');
        if (lightItem) {
            if (online) {
                lightItem.classList.remove('opacity-06');
            } else {
                lightItem.classList.add('opacity-06');
            }
        }
    }

    function toggleLight(deviceId, newState) {
        if (isToggling[deviceId]) {
            return;
        }

        isToggling[deviceId] = true;

        HttpClient.post('/widgets/lights/toggle', {
            device_id: deviceId,
            state: newState
        }).then(function(response) {
            if (response.success) {

                delete isToggling[deviceId];

                isToggling[deviceId + '_websocket_block'] = true;
                setTimeout(function() {
                    delete isToggling[deviceId + '_websocket_block'];
                }, 1000);

            } else {
                throw new Error(response.error || 'Toggle failed');
            }
        }).catch(function(error) {

            var switchElement = document.getElementById('light-switch-' + deviceId);
            if (switchElement) {
                switchElement.checked = !newState;
                updateSwitchOnly(deviceId, !newState);
            }

            if (window.DashboardUtils) {
                window.DashboardUtils.showStatusMessage('Erreur lors du contrôle de la lumière', 'danger');
            }

            delete isToggling[deviceId];
        });
    }

    function init() {
        if (window.lightsState) {
            originalRoomsData = window.lightsState || [];

            widget = new Widget('lights', {
                realtimeEvents: ['light-update', 'light-online', 'light-offline']
            });

            var flatState = {};
            PolyfillUtils.forEach(originalRoomsData, function(room) {
                if (room.lights && Array.isArray(room.lights)) {
                    PolyfillUtils.forEach(room.lights, function(light) {
                        flatState[light.id] = light;
                    });
                }
            });

            widget.initState(flatState);

            widget.onStateChange(function(deviceId, deviceState) {
                updateLightInRooms(deviceId, deviceState);

                if (deviceState.hasOwnProperty('switch_state') && !isToggling[deviceId + '_websocket_block']) {
                    updateSwitchOnly(deviceId, deviceState.switch_state);
                }

                if (deviceState.hasOwnProperty('online')) {
                    updateOnlineStatus(deviceId, deviceState.online);
                }
            });

            renderLightsView();

            setupSettingsModal();

            loadAndApplySettings();
        }
    }

    function setupSettingsModal() {

        var alphabeticRadios = document.querySelectorAll('input[name="alphabeticOrder"]');
        for (var i = 0; i < alphabeticRadios.length; i++) {
            if (alphabeticRadios[i].addEventListener) {
                alphabeticRadios[i].addEventListener('change', function() {
                    autoSaveSettings();
                });
            }
        }

        var statusRadios = document.querySelectorAll('input[name="statusPriority"]');
        for (var i = 0; i < statusRadios.length; i++) {
            if (statusRadios[i].addEventListener) {
                statusRadios[i].addEventListener('change', function() {
                    autoSaveSettings();
                });
            }
        }
    }

    function openSettingsModal() {
        window.DashboardModal.open('lightsSettingsModal', function() {
            initializeSortable();
            loadSettingsIntoModal();
        });
    }

    function initializeSortable() {
        var sortableContainer = document.getElementById('sortable-rooms');
        if (!sortableContainer || !window.Sortable) {
            return;
        }

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

    function loadSettingsIntoModal() {
        var settings = getSettings();

        var alphabeticOrder = settings.alphabeticOrder || 'a-to-z';
        var alphabeticRadio = document.getElementById('order-' + alphabeticOrder);
        if (alphabeticRadio) {
            alphabeticRadio.checked = true;
        }

        var statusPriority = settings.statusPriority || 'no-priority';
        var statusRadio = document.getElementById('priority-' + statusPriority);
        if (statusRadio) {
            statusRadio.checked = true;
        }

        if (settings.roomOrder && Object.keys(settings.roomOrder).length > 0) {
            applySavedOrderToModal(settings.roomOrder);
        }
    }

    function applySavedOrderToModal(roomOrder) {
        var container = document.getElementById('sortable-rooms');
        if (!container) return;

        var roomElements = Array.prototype.slice.call(container.children);

        roomElements.sort(function(a, b) {
            var roomIdA = a.getAttribute('data-room-id');
            var roomIdB = b.getAttribute('data-room-id');

            var weightA = roomOrder[roomIdA] || 999;
            var weightB = roomOrder[roomIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < roomElements.length; i++) {
            container.appendChild(roomElements[i]);
        }
    }

    function autoSaveSettings() {
        var settings = {
            roomOrder: getRoomOrderFromModal(),
            alphabeticOrder: getAlphabeticOrderFromModal(),
            statusPriority: getStatusPriorityFromModal()
        };

        localStorage.setItem('lights-settings', JSON.stringify(settings));

        setTimeout(function() {
            applySettings();
        }, 100);
    }

    function getRoomOrderFromModal() {
        var roomOrder = {};
        var sortableItems = document.querySelectorAll('#sortable-rooms li');

        for (var i = 0; i < sortableItems.length; i++) {
            var roomId = sortableItems[i].getAttribute('data-room-id');
            if (roomId) {
                roomOrder[roomId] = i + 1;
            }
        }

        return roomOrder;
    }

    function getAlphabeticOrderFromModal() {
        var checkedRadio = document.querySelector('input[name="alphabeticOrder"]:checked');
        return checkedRadio ? checkedRadio.value : 'a-to-z';
    }

    function getStatusPriorityFromModal() {
        var checkedRadio = document.querySelector('input[name="statusPriority"]:checked');
        return checkedRadio ? checkedRadio.value : 'no-priority';
    }

    function getSettings() {
        try {
            var settings = localStorage.getItem('lights-settings');
            return settings ? JSON.parse(settings) : {
                roomOrder: {},
                alphabeticOrder: 'a-to-z',
                statusPriority: 'no-priority'
            };
        } catch (e) {
            return {
                roomOrder: {},
                alphabeticOrder: 'a-to-z',
                statusPriority: 'no-priority'
            };
        }
    }

    function loadAndApplySettings() {

        saveOriginalRoomsData();

        setTimeout(function() {
            applySettings();
        }, 100);
    }

    function saveOriginalRoomsData() {
        originalRoomsData = [];
        var roomSections = document.querySelectorAll('[data-room-section]');

        for (var i = 0; i < roomSections.length; i++) {
            var section = roomSections[i];
            var roomName = section.querySelector('span').textContent;
            var roomElement = section.querySelector('[data-room-lights]');

            if (roomElement) {
                originalRoomsData.push({
                    name: roomName,
                    element: section,
                    originalIndex: i
                });
            }
        }
    }

    function applySettings() {
        var settings = getSettings();

        applyRoomOrder(settings.roomOrder);

        applyLightSort(settings.alphabeticOrder, settings.statusPriority);

    }

    function applyRoomOrder(roomOrder) {
        if (!roomOrder || Object.keys(roomOrder).length === 0) {
            return;
        }

        var container = document.getElementById('lights-list-view');
        if (!container) return;

        var roomSections = Array.prototype.slice.call(container.querySelectorAll('[data-room-section]'));

        roomSections.sort(function(a, b) {

            var roomIdA = getRoomIdFromSection(a);
            var roomIdB = getRoomIdFromSection(b);

            var weightA = roomOrder[roomIdA] || 999;
            var weightB = roomOrder[roomIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < roomSections.length; i++) {
            container.appendChild(roomSections[i]);
        }
    }

    function getRoomIdFromSection(section) {
        var lightItem = section.querySelector('[data-light-card][data-room-id]');
        return lightItem ? lightItem.getAttribute('data-room-id') : '';
    }

    function applyLightSort(alphabeticOrder, statusPriority) {
        var roomLights = document.querySelectorAll('[data-room-lights]');

        for (var i = 0; i < roomLights.length; i++) {
            sortLightsInRoom(roomLights[i], alphabeticOrder, statusPriority);
        }
    }

    function sortLightsInRoom(roomContainer, alphabeticOrder, statusPriority) {
        var lightColumns = Array.prototype.slice.call(roomContainer.children);

        lightColumns.sort(function(a, b) {
            var lightA = a.querySelector('[data-light-card]');
            var lightB = b.querySelector('[data-light-card]');

            if (!lightA || !lightB) return 0;

            var nameA = lightA.querySelector('span').textContent.toLowerCase();
            var nameB = lightB.querySelector('span').textContent.toLowerCase();

            var onlineA = !lightA.querySelector('input').disabled;
            var onlineB = !lightB.querySelector('input').disabled;

            if (statusPriority === 'online-first' && onlineA !== onlineB) {
                return onlineB - onlineA;
            }
            if (statusPriority === 'offline-first' && onlineA !== onlineB) {
                return onlineA - onlineB;
            }

            if (alphabeticOrder === 'z-to-a') {
                return nameB.localeCompare(nameA);
            } else {
                return nameA.localeCompare(nameB);
            }
        });

        for (var i = 0; i < lightColumns.length; i++) {
            roomContainer.appendChild(lightColumns[i]);
        }
    }

    return {
        init: init,
        toggleLight: toggleLight,
        renderLightsView: renderLightsView,
        openSettingsModal: openSettingsModal,
        updateSwitchOnly: updateSwitchOnly
    };
})();

window.toggleLight = function(deviceId, newState) {
    if (window.DashboardLights && window.DashboardLights.toggleLight) {
        window.DashboardLights.toggleLight(deviceId, newState);
    }
};

window.toggleLightFromCard = function(deviceId) {
    var switchElement = document.getElementById('light-switch-' + deviceId);
    if (switchElement && !switchElement.disabled) {
        var newState = !switchElement.checked;

        switchElement.checked = newState;
        if (window.DashboardLights && window.DashboardLights.updateSwitchOnly) {
            window.DashboardLights.updateSwitchOnly(deviceId, newState);
        }

        window.toggleLight(deviceId, newState);
    }
};

window.openLightsSettingsModal = function() {
    if (window.DashboardLights && window.DashboardLights.openSettingsModal) {
        window.DashboardLights.openSettingsModal();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardLights) {
        window.DashboardLights.init();
    }
});
