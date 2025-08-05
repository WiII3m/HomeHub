<div class="panel panel-default">
    <div class="panel-body">
        <div id="cameras-dynamic-container">
            <!-- Le contenu sera généré dynamiquement par le js -->
             <div class="text-center padding-vertical-32">
                <div class="text-gray-light">
                    <span class="font-size-2xl display-block margin-bottom-8">🔍</span>
                    Aucune caméra trouvée
                </div>
            </div>
        </div>
    </div>
</div>

@include('widgets::Cameras.resources.views.modals.cameras-settings')

<script type="text/javascript">
    window.camerasState = {!! json_encode($cameras) !!};

    window.camerasWidgetConfig = {
        icon: '{{ $config['icon'] }}',
        title: '{{ $config['title'] }}'
    };
</script>

