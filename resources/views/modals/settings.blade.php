<!-- Modal Réglages -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-radius-12">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="settingsModalLabel">
                    <span class="glyphicon glyphicon-cog margin-right-8"></span>
                    Réglages
                </h4>
            </div>
            <div class="modal-body">
                <!-- Onglets -->
                <ul class="nav nav-tabs margin-bottom-16" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#general-tab" aria-controls="general-tab" role="tab" data-toggle="tab">
                            <span class="glyphicon glyphicon-list margin-right-4"></span>
                            Général
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#security-tab" aria-controls="security-tab" role="tab" data-toggle="tab">
                            <span class="glyphicon glyphicon-lock margin-right-4"></span>
                            Sécurité
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#system-tab" aria-controls="system-tab" role="tab" data-toggle="tab">
                            <span class="glyphicon glyphicon-cog margin-right-4"></span>
                            Système
                        </a>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content">
                    <!-- Onglet Général -->
                    <div role="tabpanel" class="tab-pane active" id="general-tab">
                        <!-- Ordre des widgets -->
                        <div class="margin-bottom-24">
                            <h5 class="margin-bottom-12">
                                <span class="glyphicon glyphicon-move margin-right-8"></span>
                                Ordre des widgets
                            </h5>
                            <div class="alert alert-info font-size-medium margin-bottom-16">
                                <strong>💡 Astuce :</strong> Glissez-déposez les widgets pour réorganiser leur ordre d'affichage.
                            </div>

                            <ul id="sortable-widgets" class="list-unstyled margin-top-0">
                                @if(isset($widgetsData))
                                    @foreach($widgetsData as $widgetName => $widgetData)
                                    <li data-widget-id="widget-{{ $widgetName }}" class="sortable-widget-item bg-gray-light border-1 border-gray border-radius-4 padding-12 margin-bottom-8">
                                        <div class="row">
                                            <div class="col-xs-1 text-center">
                                                <span class="handle text-gray-light font-size-large line-height-none">⋮⋮</span>
                                            </div>
                                            <div class="col-xs-10">
                                                <span class="font-weight-500">{{ $widgetData['icon'] ?? '📱' }} {{ $widgetData['title'] ?? ucfirst($widgetName) }}</span>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                @endif
                            </ul>

                            <style>
                            #sortable-widgets {
                                position: relative;
                                z-index: 10;
                            }
                            #sortable-widgets li {
                                cursor: move;
                                position: relative;
                                z-index: 20;
                            }
                            .modal-body {
                                overflow: visible !important;
                            }
                            </style>

                            <div class="margin-top-16 font-size-small text-gray">
                                <strong>💾 Sauvegarde :</strong> L'ordre est sauvegardé automatiquement.
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Sécurité -->
                    <div role="tabpanel" class="tab-pane" id="security-tab">
                        <!-- Changement de PIN -->
                        <div class="margin-bottom-24">
                            <h5 class="margin-bottom-12">
                                <span class="glyphicon glyphicon-lock margin-right-8"></span>
                                Code PIN
                            </h5>

                            <form id="changePinForm">
                                <div class="form-group">
                                    <label>PIN actuel</label>
                                    <input type="password" class="form-control" id="currentPin" maxlength="6" placeholder="••••">
                                </div>
                                <div class="form-group">
                                    <label>Nouveau PIN</label>
                                    <input type="password" class="form-control" id="newPin" maxlength="6" placeholder="••••">
                                </div>
                                <div class="form-group">
                                    <label>Confirmer le nouveau PIN</label>
                                    <input type="password" class="form-control" id="confirmPin" maxlength="6" placeholder="••••">
                                </div>

                                <div id="pinChangeResult" class="margin-bottom-12"></div>

                                <button type="submit" class="btn btn-warning">
                                    <span class="glyphicon glyphicon-lock margin-right-4"></span>
                                    Modifier le PIN
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Onglet Système -->
                    <div role="tabpanel" class="tab-pane" id="system-tab">
                        <!-- Actions système -->
                        <div class="margin-bottom-24">
                            <h5 class="margin-bottom-12">
                                <span class="glyphicon glyphicon-wrench margin-right-8"></span>
                                Actions
                            </h5>
                            <div class="row">
                                <div class="col-xs-6">
                                    <button class="btn btn-primary btn-block" onclick="refreshDashboard()">
                                        <span class="glyphicon glyphicon-refresh margin-right-4"></span>
                                        Actualiser
                                    </button>
                                </div>
                                <div class="col-xs-6">
                                    <form method="POST" action="/pin/logout" style="display: inline; width: 100%;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <span class="glyphicon glyphicon-log-out margin-right-4"></span>
                                            Déconnexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Gestion des widgets -->
                        <div class="margin-bottom-16">
                            <h5 class="margin-bottom-12">
                                <span class="glyphicon glyphicon-th-large margin-right-8"></span>
                                Widgets installés
                            </h5>
                            <div class="alert alert-info font-size-medium margin-bottom-16">
                                <strong>💡 Info :</strong> Activez ou désactivez les widgets selon vos besoins.
                            </div>

                            <div id="widgets-list">
                                @if(isset($widgetsData))
                                    @foreach($widgetsData as $widgetName => $widgetData)
                                    <div class="widget-control-item bg-gray-light border-1 border-gray border-radius-4 padding-12 margin-bottom-8">
                                        <div class="row">
                                            <div class="col-xs-8">
                                                <div class="margin-bottom-4">
                                                    <span class="font-weight-500">{{ $widgetData['icon'] ?? '📱' }} {{ $widgetData['title'] ?? ucfirst($widgetName) }}</span>
                                                </div>
                                                <div class="font-size-small text-gray">
                                                    <span>Version : {{ $widgetData['version'] ?? '1.0.0' }}</span>
                                                    @if(isset($widgetData['author']))
                                                        <span> - Auteur : {{ $widgetData['author'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-xs-4 text-right">
                                                <label class="switch">
                                                    <input type="checkbox"
                                                           id="widget-switch-{{ $widgetName }}"
                                                           checked
                                                           onchange="toggleWidget('{{ $widgetName }}', this.checked)">
                                                    <span class="slider">
                                                        <span class="slider-handle"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="margin-top-16 font-size-small text-gray">
                                <strong>💾 Sauvegarde :</strong> Les changements sont appliqués automatiquement.
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour basculer l'activation d'un widget
    function toggleWidget(widgetName, enabled) {
        // Récupérer les settings actuels
        var settings = getWidgetSettings();
        var widgetId = 'widget-' + widgetName;

        // Initialiser si n'existe pas
        if (!settings[widgetId]) {
            settings[widgetId] = { position: 999, enabled: true };
        }

        // Mettre à jour l'état enabled
        settings[widgetId].enabled = enabled;

        // Sauvegarder
        localStorage.setItem('widget-settings', JSON.stringify(settings));

        // Appliquer immédiatement sur le dashboard
        if (window.DashboardLayout) {
            window.DashboardLayout.applyWidgetOrder(settings);
        }

        // Changement optimiste de l'état du switch - le CSS fait le reste !
        var checkbox = document.getElementById('widget-switch-' + widgetName);
        if (checkbox) {
            checkbox.checked = enabled;
        }
    }

    // Fonction helper pour récupérer les settings (cohérent avec layout.js)
    function getWidgetSettings() {
        try {
            var settings = localStorage.getItem('widget-settings');
            return settings ? JSON.parse(settings) : {};
        } catch (e) {
            return {};
        }
    }

    // Fonction pour actualiser le dashboard
    function refreshDashboard() {
        location.reload();
    }

</script>
