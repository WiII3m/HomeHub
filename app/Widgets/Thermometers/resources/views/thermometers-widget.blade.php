<div class="panel panel-default">
    <div class="panel-body">
        <!-- Conteneur dynamique pour les vues (header inclus) -->
        <div id="thermometers-dynamic-container">
            <!-- Les cards seront généré par JavaScript via les templates -->
            <div class="text-center padding-vertical-32">
                <div class="text-gray-light">
                    <span class="font-size-2xl display-block margin-bottom-8">🔍</span>
                    Aucun thermomètre trouvé
                </div>
            </div>
        </div>
    </div>
</div>

@include('widgets::Thermometers.resources.views.modals.settings')

<!-- Injection de l'état initial pour JavaScript -->
<script>
    // Injecter les données PHP dans l'état JavaScript
    window.thermometersState = @json($thermometers);

    window.thermometersWidgetConfig = {
        icon: '{{ $config['icon'] }}',
        title: '{{ $config['title'] }}'
    };
</script>
