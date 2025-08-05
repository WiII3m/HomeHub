window.DashboardCameras = (function() {
    'use strict';

    var widget = null;
    var currentCameraId = null;
    var isStreamActive = false;
    var sortableInstance = null;

    var placeholderBase = "/widgets/cameras/public/images/";
    var placeholders = {
        true: { // online
            true: 'camera-placeholder-private.png', // privacyMode
            false: 'camera-placeholder-online.png', // !privacyMode
        },
        false: 'camera-placeholder-offline.png' // !online
    };

    function createCameraCard(cameraId, data) {
        var name = data.name || 'Caméra';
        var online = data.online != null ? data.online : false;
        var privacyMode = data.basic_private != null ? data.basic_private : false;
        var placeholderSrc = `${placeholderBase}${placeholders[online][privacyMode] || placeholders.false}`;

        return `
            <div class="col-sm-6 col-xs-12 margin-bottom-16">
                <div class="bg-gray-light border-radius-6 padding-16 cursor-pointer hover-gray-lighter active-gray-lightest"
                     data-camera-id="${cameraId}"
                     onclick="selectCameraWithCurrentState('${cameraId}', '${name}')">

                    <div class="margin-bottom-16 line-height-tight display-table width-full">
                        <span class="font-weight-500 text-gray-dark font-size-medium display-table-cell">${name}</span>
                        <div class="font-size-small text-gray-light display-table-cell text-right">
                            <span id="camera-status-${cameraId}" class="status-point ${online ? 'online' : 'offline'} border-radius-50 margin-right-4 display-inline-block vertical-align-middle"></span>
                            <span id="camera-status-text-${cameraId}" class="vertical-align-middle">${online ? 'En ligne' : 'Hors ligne'}</span>
                        </div>
                    </div>

                    <div class="text-center margin-bottom-12">
                        <img src="${placeholderSrc}" class="camera-placeholder width-full height-72 opacity-06 display-block height-auto margin-auto">
                    </div>

                    ${privacyMode && online ? `
                        <div class="text-center font-size-small text-warning margin-bottom-8">
                            <span class="glyphicon glyphicon-eye-close margin-right-4"></span>
                            Mode privé activé
                        </div>
                    ` : ''}

                    <div class="text-center font-size-small text-gray-light">
                        <span>Cliquer pour contrôler</span>
                    </div>
                </div>
            </div>
        `;
    }

    function createCameraDetailView(cameraId, data) {
        var name = data.name || 'Caméra';
        var online = data.online != null ? data.online : false;
        var statusText = online ? 'En ligne' : 'Hors ligne';
        var statusClass = online ? 'online' : 'offline';

        return `
            <div id="camera-detail-view">
                <div class="row margin-bottom-16">
                    <div class="col-xs-12">
                        <h2 class="h4 margin-top-0 display-flex justify-content-space-between align-items-center">
                            <div>
                                <span class="font-size-large margin-right-8">📹</span>
                                <span>${name}</span>
                            </div>
                            <div class="font-size-small text-gray-light">
                                <span id="camera-header-dot-${cameraId}" class="status-point ${statusClass} border-radius-50 margin-right-4 display-inline-block vertical-align-middle"></span>
                                <span id="camera-header-text-${cameraId}" class="vertical-align-middle">${statusText}</span>
                            </div>
                        </h2>
                    </div>
                </div>

                <div class="row margin-bottom-24">
                    <div class="col-xs-12">
                        <button class="bg-transparent border-0 text-primary cursor-pointer font-size-medium" data-back-button>
                            ← Retour à la liste
                        </button>
                    </div>
                </div>

                <div class="row margin-bottom-24">
                    <div class="col-xs-12">
                        <div class="camera-container">
                            <div id="camera-video-container-${cameraId}">
                                ${getVideoContainerPlaceholder(data)}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row margin-bottom-24">
                    <div class="col-xs-12">
                        <div class="ptz-controls">
                            <div class="ptz-grid">
                                <div class="ptz-row">
                                    <div class="ptz-cell"></div>
                                    <div class="ptz-cell">
                                        <button class="ptz-button" data-ptz-button data-camera-id="${cameraId}" data-direction="up" ${!online ? 'disabled' : ''}>
                                            <span>▲</span>
                                        </button>
                                    </div>
                                    <div class="ptz-cell"></div>
                                </div>

                                <div class="ptz-row">
                                    <div class="ptz-cell">
                                        <button class="ptz-button ptz-left" data-ptz-button data-camera-id="${cameraId}" data-direction="left" ${!online ? 'disabled' : ''}>
                                            <span>▲</span>
                                        </button>
                                    </div>
                                    <div class="ptz-cell">
                                        <button id="camera-power-btn-${cameraId}" class="ptz-button ptz-power" ${!online ? 'disabled' : ''}>
                                            <span>
                                                <img class="height-24 width-24 margin-top-neg-8" src="/widgets/cameras/public/images/button-io.png">
                                            </span>
                                        </button>
                                    </div>
                                    <div class="ptz-cell">
                                        <button class="ptz-button ptz-right" data-ptz-button data-camera-id="${cameraId}" data-direction="right" ${!online ? 'disabled' : ''}>
                                            <span>▲</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="ptz-row">
                                    <div class="ptz-cell"></div>
                                    <div class="ptz-cell">
                                        <button class="ptz-button" data-ptz-button data-camera-id="${cameraId}" data-direction="down" ${!online ? 'disabled' : ''}>
                                            <span>▼</span>
                                        </button>
                                    </div>
                                    <div class="ptz-cell"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function getVideoContainerPlaceholder(data) {
        var online = data.online != null ? data.online : false;
        var privacyMode = data.basic_private != null ? data.basic_private : false;
        var placeholderSrc = `${placeholderBase}${placeholders[online][privacyMode] || placeholders.false}`;

        return `<img src="${placeholderSrc}" class="camera-media">`;
    }

    function sortDeviceIds(deviceIds, camerasOrder) {
        if (!camerasOrder || Object.keys(camerasOrder).length === 0) return deviceIds;

        return deviceIds.slice().sort(function(a, b) {
            var weightA = camerasOrder[a] || 999;
            var weightB = camerasOrder[b] || 999;
            return weightA - weightB;
        });
    }

    function renderCameraCards() {
        var container = document.getElementById('cameras-dynamic-container');
        if (!container) return;

        // Trier les device IDs selon les settings
        var deviceIds = Object.keys(window.camerasState || {});
        var settings = getSettings();
        var sortedDeviceIds = sortDeviceIds(deviceIds, settings.camerasOrder);

        // Générer les cards triées
        var cards = '';
        PolyfillUtils.forEach(sortedDeviceIds, function(cameraId) {
            var cameraData = widget.getDeviceState(cameraId);
            cards += createCameraCard(cameraId, cameraData);
        });

        // Header + cards d'un coup
        container.innerHTML = `
            <div class="row">
                <div class="col-xs-8">
                    <h2 class="h4 margin-top-0">
                        <span class="font-size-large margin-right-8 vertical-align-middle">${window.camerasWidgetConfig.icon}</span>
                        <span class="vertical-align-middle">${window.camerasWidgetConfig.title}</span>
                    </h2>
                </div>
                <div class="col-xs-4 text-right">
                    <button class="btn btn-default btn-sm margin-top-4 border-0 font-size-large width-48 padding-0" data-settings-button>
                        <span class="font-size-base">⚙️</span>
                    </button>
                </div>
            </div>
            <div class="row margin-top-16" data-cameras-list>
                ${cards}
            </div>
        `;

        currentCameraId = null;
        isStreamActive = false;
        setupEventDelegation();
    }

    function setupEventDelegation() {
        var container = document.getElementById('cameras-dynamic-container');
        if (!container) return;

        // Event delegation pour tous les clics dans le widget cameras
        container.addEventListener('click', function(e) {
            // Clic sur une carte de caméra
            var cameraCard = e.target.closest('[data-camera-id]');
            if (cameraCard && !e.target.closest('button')) {
                var cameraId = cameraCard.getAttribute('data-camera-id');
                var nameElement = cameraCard.querySelector('.font-weight-500');
                var cameraName = nameElement ? nameElement.textContent : 'Caméra';
                selectCameraWithCurrentState(cameraId, cameraName);
                return;
            }

            // Clic sur le bouton settings
            var settingsButton = e.target.closest('[data-settings-button]');
            if (settingsButton) {
                openSettingsModal();
                return;
            }

            // Clic sur le bouton power de caméra
            var powerButton = e.target.closest('#camera-power-btn-' + currentCameraId);
            if (powerButton) {
                isStreamActive ? stopStreamAndSetPrivate() : startStreamWithPrivacyCheck();
                return;
            }

            // Clic sur un bouton PTZ
            var ptzButton = e.target.closest('[data-ptz-button]');
            if (ptzButton && isStreamActive) {
                var direction = ptzButton.getAttribute('data-direction').toUpperCase();
                controlCameraPtz(direction);
                return;
            }

            // Clic sur le bouton retour
            var backButton = e.target.closest('[data-back-button]');
            if (backButton) {
                renderCameraCards();
                return;
            }
        });
    }

    function showCameraDetail(cameraId) {
        var container = document.getElementById('cameras-dynamic-container');
        if (!container) return;

        var cameraData = widget.getDeviceState(cameraId);
        if (!cameraData) return;

        currentCameraId = cameraId;
        container.innerHTML = createCameraDetailView(cameraId, cameraData);
    }

    function startStreamWithPrivacyCheck() {
        updateCameraButtons('loading');

        HttpClient.post('/widgets/cameras/toggle', {
            enable: true,
            device_id: currentCameraId
        }).then(function(data) {
            if (data.success) {
                startLiveStreamDirect();
            } else {
                if (data.error && (data.error.includes('device is offline') || data.error.includes('offline'))) {
                    handleCameraOffline();
                } else {
                    updateCameraButtons('idle');
                    window.DashboardUtils.showStatusMessage('Erreur: ' + data.error, 'error');
                }
            }
        }).catch(function(error) {
            if (error.message && (error.message.includes('device is offline') || error.message.includes('offline'))) {
                handleCameraOffline();
            } else {
                updateCameraButtons('idle');
                window.DashboardUtils.showStatusMessage('Erreur: ' + error.message, 'error');
            }
        });
    }

    function startLiveStreamDirect() {
        HttpClient.get('/widgets/cameras/stream?device_id=' + encodeURIComponent(currentCameraId))
        .then(function(data) {
            if (data.success) {
                setupVideoElement(data.data.url);
            } else {
                updateCameraButtons('idle');
                window.DashboardUtils.showStatusMessage('Erreur: ' + data.error, 'error');
            }
        }).catch(function(error) {
            updateCameraButtons('idle');
            window.DashboardUtils.showStatusMessage('Erreur: ' + error.message, 'error');
        });
    }

    function setupVideoElement(videoUrl) {
        var videoContainer = document.getElementById('camera-video-container-' + currentCameraId);
        if (!videoContainer) return;

        var videoElement = document.createElement('video');
        videoElement.src = videoUrl;
        videoElement.controls = true;
        videoElement.autoplay = true;
        videoElement.muted = true;
        videoElement.preload = 'metadata';
        videoElement.setAttribute('playsinline', 'true');
        videoElement.setAttribute('webkit-playsinline', 'true');
        videoElement.setAttribute('x-webkit-airplay', 'allow');
        videoElement.className = 'camera-media';

        videoElement.onloadstart = function() {
            window.DashboardUtils.showStatusMessage('Connexion à la caméra...', 'info');
        };

        videoElement.onloadedmetadata = function() {
            attemptAutoplay(videoElement);
        };

        videoElement.oncanplay = function() {
            attemptAutoplay(videoElement);
        };

        videoElement.onerror = function() {
            updateCameraButtons('idle');
            window.DashboardUtils.showStatusMessage('Erreur de lecture vidéo', 'error');
        };

        videoContainer.innerHTML = '';
        videoContainer.appendChild(videoElement);

        isStreamActive = true;
        updateCameraButtons('active');
    }

    function attemptAutoplay(videoElement) {
        if (videoElement && videoElement.paused) {
            try {
                var playPromise = videoElement.play();
                if (playPromise && typeof playPromise.then === 'function') {
                    playPromise.then(function() {
                    }).catch(function(error) {
                    });
                }
            } catch (error) {
            }
        }
    }

    function stopStreamAndSetPrivate() {
        updateCameraButtons('loading');

        var videoContainer = document.getElementById('camera-video-container-' + currentCameraId);
        if (videoContainer)
            videoContainer.innerHTML = getVideoContainerPlaceholder({online : true, basic_private : true});

        HttpClient.post('/widgets/cameras/toggle', {
            enable: false,
            device_id: currentCameraId
        }).then(function(data) {
            if (data.success) {
                isStreamActive = false;
                updateCameraButtons('idle');
                window.DashboardUtils.showStatusMessage('Caméra remise en mode privé', 'success');
            } else {
                updateCameraButtons('idle');
                window.DashboardUtils.showStatusMessage('Erreur: ' + data.error, 'error');
            }
        }).catch(function(error) {
            updateCameraButtons('idle');
            window.DashboardUtils.showStatusMessage('Erreur: ' + error.message, 'error');
        });
    }

    function controlCameraPtz(direction) {
        if (!currentCameraId) {
            window.DashboardUtils.showStatusMessage('Aucune caméra sélectionnée', 'error');
            return;
        }

        HttpClient.post('/widgets/cameras/ptz', {
            direction: direction,
            device_id: currentCameraId
        }).then(function(data) {
            if (data.success) {
                window.DashboardUtils.showStatusMessage('Commande PTZ envoyée', 'success');
            } else {
                window.DashboardUtils.showStatusMessage('Erreur PTZ: ' + data.error, 'error');
            }
        }).catch(function(error) {
            window.DashboardUtils.showStatusMessage('Erreur PTZ: ' + error.message, 'error');
        });
    }

    function updateCameraButtons(state) {
        var button = document.getElementById('camera-power-btn-' + currentCameraId);
        if (button) {
            button.className = 'ptz-button ptz-power';
            if (state === 'loading') {
                button.className += ' loading';
                button.disabled = true;
            } else if (state === 'active') {
                button.className += ' active';
                button.disabled = false;
            } else {
                button.disabled = false;
            }
        }
    }

    function updateCameraStatus(cameraId, online) {
        var statusElement = document.getElementById('camera-status-' + cameraId);
        var statusTextElement = document.getElementById('camera-status-text-' + cameraId);

        if (statusElement) {
            statusElement.className = statusElement.className.replace(/\b(online|offline)\b/g, '');
            statusElement.classList.add(online ? 'online' : 'offline');
        }

        if (statusTextElement) {
            statusTextElement.textContent = online ? 'En ligne' : 'Hors ligne';
        }

        if (currentCameraId === cameraId) {
            var headerStatusElement = document.getElementById('camera-header-dot-' + cameraId);
            var headerStatusTextElement = document.getElementById('camera-header-text-' + cameraId);

            if (headerStatusElement) {
                headerStatusElement.className = headerStatusElement.className.replace(/\b(online|offline)\b/g, '');
                headerStatusElement.classList.add(online ? 'online' : 'offline');
            }

            if (headerStatusTextElement) {
                headerStatusTextElement.textContent = online ? 'En ligne' : 'Hors ligne';
            }


            updateCameraDisplay(cameraId);
        }
    }

    function updateCameraDisplay(cameraId) {
        if (currentCameraId !== cameraId) return;

        var cameraData = widget.getDeviceState(cameraId);
        var videoContainer = document.getElementById('camera-video-container-' + cameraId);

        if (videoContainer) {
            videoContainer.innerHTML = getVideoContainerPlaceholder(cameraData);
        }

        updateCameraButtons('idle');
    }

    function handleCameraOffline() {
        if (currentCameraId && widget) {
            widget.updateDeviceState(currentCameraId, { online: false });
        }

        var ptzButtons = document.querySelectorAll('[data-ptz-button][data-camera-id="' + currentCameraId + '"]');
        for (var i = 0; i < ptzButtons.length; i++) {
            ptzButtons[i].disabled = true;
        }

        var powerButton = document.getElementById('camera-power-btn-' + currentCameraId);
        if (powerButton) {
            powerButton.disabled = true;
        }

        var videoContainer = document.getElementById('camera-video-container-' + currentCameraId);
        if (videoContainer) {
            videoContainer.innerHTML = getVideoContainerPlaceholder({online: false});
        }

        isStreamActive = false;
        updateCameraButtons('idle');
    }

    function openSettingsModal() {
        window.DashboardModal.open('cameraSettingsModal', function() {
            initializeSortable();
            loadSettingsIntoModal();
        });
    }

    function initializeSortable() {
        var sortableContainer = document.getElementById('sortable-cameras');
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
        if (settings.camerasOrder && Object.keys(settings.camerasOrder).length > 0) {
            applySavedOrderToModal(settings.camerasOrder);
        }
    }

    function applySavedOrderToModal(camerasOrder) {
        var container = document.getElementById('sortable-cameras');
        if (!container) return;

        var cameraElements = Array.prototype.slice.call(container.children);

        cameraElements.sort(function(a, b) {
            var cameraIdA = a.getAttribute('data-camera-id');
            var cameraIdB = b.getAttribute('data-camera-id');
            var weightA = camerasOrder[cameraIdA] || 999;
            var weightB = camerasOrder[cameraIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < cameraElements.length; i++) {
            container.appendChild(cameraElements[i]);
        }
    }

    function autoSaveSettings() {
        var settings = {
            camerasOrder: getCamerasOrderFromModal()
        };

        localStorage.setItem('cameras-settings', JSON.stringify(settings));

        setTimeout(function() {
            applyCamerasOrder(settings.camerasOrder);
        }, 100);
    }

    function getCamerasOrderFromModal() {
        var camerasOrder = {};
        var sortableItems = document.querySelectorAll('#sortable-cameras li');

        for (var i = 0; i < sortableItems.length; i++) {
            var cameraId = sortableItems[i].getAttribute('data-camera-id');
            if (cameraId) {
                camerasOrder[cameraId] = i + 1;
            }
        }

        return camerasOrder;
    }

    function getSettings() {
        try {
            var settings = localStorage.getItem('cameras-settings');
            return settings ? JSON.parse(settings) : {
                camerasOrder: {}
            };
        } catch (e) {
            return {
                camerasOrder: {}
            };
        }
    }

    function applyCamerasOrder(camerasOrder) {
        if (!camerasOrder || Object.keys(camerasOrder).length === 0) {
            return;
        }

        var container = document.querySelector('[data-cameras-list]');
        if (!container) {
            return;
        }

        var cameraColumns = Array.prototype.slice.call(container.children);

        cameraColumns.sort(function(a, b) {
            var cameraIdA = a.querySelector('[data-camera-id]').getAttribute('data-camera-id');
            var cameraIdB = b.querySelector('[data-camera-id]').getAttribute('data-camera-id');

            var weightA = camerasOrder[cameraIdA] || 999;
            var weightB = camerasOrder[cameraIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < cameraColumns.length; i++) {
            container.appendChild(cameraColumns[i]);
        }
    }


    function init() {
        if (window.camerasState) {
            widget = new Widget('cameras', {
                realtimeEvents: ['camera-update', 'camera-online', 'camera-offline']
            });

            widget.initState(window.camerasState);

            widget.onStateChange(function(cameraId, cameraState) {
                if (cameraState.hasOwnProperty('online')) {
                    updateCameraStatus(cameraId, cameraState.online);
                }

                if (cameraState.hasOwnProperty('basic_private') ||
                    cameraState.hasOwnProperty('stream_url')) {
                    updateCameraDisplay(cameraId);
                }

                if (!currentCameraId) {
                    renderCameraCards();
                }
            });

            renderCameraCards();
        }
    }

    return {
        init,
        showCameraDetail,
        renderCameraCards,
        openSettingsModal,
        updateCameraStatus
    };
})();

window.selectCameraWithCurrentState = function(cameraId, cameraName) {
    if (window.DashboardCameras && window.DashboardCameras.showCameraDetail) {
        window.DashboardCameras.showCameraDetail(cameraId);
    }
};

window.showCameraCards = function() {
    if (window.DashboardCameras && window.DashboardCameras.renderCameraCards) {
        window.DashboardCameras.renderCameraCards();
    }
};

window.openCameraSettingsModal = function() {
    if (window.DashboardCameras && window.DashboardCameras.openSettingsModal) {
        window.DashboardCameras.openSettingsModal();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardCameras) {
        window.DashboardCameras.init();
    }
});
