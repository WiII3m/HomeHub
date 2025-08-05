<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-8">
                <h2 class="h4 margin-top-0">
                    <span class="font-size-large margin-right-8 vertical-align-middle">{{ $config['icon'] }}</span>
                    <span id="lights-widget-title" class="vertical-align-middle">{{ $config['title'] }}</span>
                </h2>
            </div>
            <div class="col-xs-4 text-right">
                <button id="lights-settings-btn" class="btn btn-default btn-sm margin-top-4 border-0 font-size-large width-48 padding-0" onclick="openLightsSettingsModal()">
                    <span class="font-size-base">⚙️</span>
                </button>
            </div>
        </div>

        <!-- Conteneur dynamique pour les lumières (géré par JavaScript) -->
        <div id="lights-dynamic-container">
            <!-- Le contenu sera généré dynamiquement -->
        </div>
    </div>
</div>

@include('widgets::Lights.resources.views.modals.settings')

<!-- Injection de l'état initial pour JavaScript -->
<script>
    // Injecter les données PHP dans l'état JavaScript
    window.lightsState = @json($lightsByRoom);
</script>

