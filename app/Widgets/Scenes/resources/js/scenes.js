window.DashboardScenes = (function() {
    'use strict';

    var sortableInstance = null;

    function init() {
        setupSceneListeners();
        applySettings();
    }

    function setupSceneListeners() {
        var sceneButtons = document.querySelectorAll('[data-scene-id]');

        PolyfillUtils.forEach(sceneButtons, function(button) {
            var sceneId = button.getAttribute('data-scene-id');

            button.addEventListener('click', function() {
                triggerScene(sceneId, button);
            });

        });
    }

    function triggerScene(sceneId, buttonElement) {
        if (!sceneId) {
            if (window.DashboardUtils) {
                window.DashboardUtils.showStatusMessage('ID de scène manquant', 'error');
            }
            return;
        }

        var nameElement, originalText;
        if (buttonElement) {
            buttonElement.classList.add('loading');

            nameElement = buttonElement.querySelector('[data-scene-name]');
            originalText = nameElement ? nameElement.textContent : '';
            if (nameElement) {
                nameElement.textContent = 'Activation...';
            }
        }

        HttpClient.post('/widgets/scenes/trigger/' + sceneId, {
            scene_id: sceneId
        }).then(function(response) {
            if (response.success) {
                if (window.DashboardUtils) {
                    window.DashboardUtils.showStatusMessage('Scène activée avec succès', 'success');
                }

                buttonElement.classList.remove('loading');

                if (buttonElement) {
                    buttonElement.classList.add('success');
                    setTimeout(function() {
                        buttonElement.classList.remove('success');
                    }, 1500);
                }
            } else {
                throw new Error(response.error || 'Scène non activée');
            }
        }).catch(function(error) {
            if (window.DashboardUtils) {
                window.DashboardUtils.showStatusMessage('Erreur: ' + error.message, 'error');
            }
        }).finally(function() {
            if (buttonElement) {
                buttonElement.classList.remove('loading');

                if (nameElement) {
                    nameElement.textContent = originalText;
                }
            }
        });
    }

    function openSettingsModal() {
        window.DashboardModal.open('scenesSettingsModal', function() {
            initializeSortable();
            setSettingsIntoModal();
        });
    }

    function initializeSortable() {
        var sortableContainer = document.getElementById('sortable-scenes');
        if (!sortableContainer || !window.Sortable) {
            return;
        }

        if (sortableInstance) {
            sortableInstance.destroy();
        }

        sortableInstance = window.Sortable.create(sortableContainer, {
            onEnd: function() {
                saveSettings();
            }
        });
    }

    function setSettingsIntoModal() {
        var settings = getSettings();

        if (settings.scenesOrder && Object.keys(settings.scenesOrder).length > 0) {
            applyOrderSettingsToModalSortableElements(settings.scenesOrder);
        }
    }

    function applyOrderSettingsToModalSortableElements(scenesOrder) {
        var container = document.getElementById('sortable-scenes');
        if (!container) return;

        var sceneElements = Array.prototype.slice.call(container.children);

        sceneElements.sort(function(a, b) {
            var sceneIdA = a.getAttribute('data-scene-id');
            var sceneIdB = b.getAttribute('data-scene-id');

            var weightA = scenesOrder[sceneIdA] || 999;
            var weightB = scenesOrder[sceneIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < sceneElements.length; i++) {
            container.appendChild(sceneElements[i]);
        }
    }

    function saveSettings() {
        var settings = {
            scenesOrder: getScenesOrderFromModal()
        };

        localStorage.setItem('scenes-settings', JSON.stringify(settings));

        setTimeout(function() {
            applySettings();
        }, 100);
    }

    function getScenesOrderFromModal() {
        var scenesOrder = {};
        var sortableItems = document.querySelectorAll('#sortable-scenes li');

        for (var i = 0; i < sortableItems.length; i++) {
            var sceneId = sortableItems[i].getAttribute('data-scene-id');
            if (sceneId) {
                scenesOrder[sceneId] = i + 1;
            }
        }

        return scenesOrder;
    }

    function getSettings() {
        try {
            var settings = localStorage.getItem('scenes-settings');

            return settings ? JSON.parse(settings) : {
                scenesOrder: {}
            };
        } catch (e) {
            return {
                scenesOrder: {}
            };
        }
    }

    function applySettings() {
        var settings = getSettings()
        applyScenesOrder(settings.scenesOrder)
    }

    function applyScenesOrder(scenesOrder) {
        if (!scenesOrder || Object.keys(scenesOrder).length === 0) {
            return;
        }

        var container = document.querySelector('[data-scenes-container]');
        if (!container) {
            return;
        }

        var sceneColumns = Array.prototype.slice.call(container.children);

        sceneColumns.sort(function(a, b) {
            var sceneIdA = a.querySelector('[data-scene-id]').getAttribute('data-scene-id');
            var sceneIdB = b.querySelector('[data-scene-id]').getAttribute('data-scene-id');

            var weightA = scenesOrder[sceneIdA] || 999;
            var weightB = scenesOrder[sceneIdB] || 999;

            return weightA - weightB;
        });

        for (var i = 0; i < sceneColumns.length; i++) {
            container.appendChild(sceneColumns[i]);
        }
    }

    return {
        init,
        triggerScene,
        openSettingsModal
    };
})();

window.openScenesSettingsModal = function() {
    if (window.DashboardScenes && window.DashboardScenes.openSettingsModal) {
        window.DashboardScenes.openSettingsModal();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardScenes) {
        window.DashboardScenes.init();
    }
});
