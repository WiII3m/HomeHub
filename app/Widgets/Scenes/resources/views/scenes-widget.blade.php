<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-8">
                <h2 class="h4 margin-top-0">
                    <span class="font-size-large margin-right-8 vertical-align-middle">{{ $config['icon'] }}</span>
                    <span class="vertical-align-middle">{{ $config['title'] }}</span>
                </h2>
            </div>
            <div class="col-xs-4 text-right">
                <button id="scenes-settings-btn" class="btn btn-default btn-sm margin-top-4 border-0 font-size-large width-48 padding-0" onclick="openScenesSettingsModal()">
                    <span class="font-size-base">⚙️</span>
                </button>
            </div>
        </div>

        @if(empty($scenes))
            <div class="text-center padding-vertical-32">
                <div class="text-gray-light">
                    <span class="font-size-2xl display-block margin-bottom-8">🔍</span>
                    Aucune scène d'éclairage trouvée
                </div>
            </div>
        @else
            <div class="row margin-top-16" data-scenes-container>
                @foreach($scenes as $scene)
                    <div class="col-xs-6 margin-bottom-16">
                        <div class="scene-item bg-gray-light border-radius-6 padding-16 padding-right-0 height-full cursor-pointer transition-bg hover-gray-lighter active-gray-lightest"
                             data-scene-id="{{ $scene['id'] }}">

                            <div class="row display-table width-full height-40">
                                <div class="col-xs-8 display-table-cell vertical-align-middle float-none">
                                    <span class="scene-name font-weight-500 text-gray-dark font-size-medium" data-scene-name>{{ $scene['name'] }}</span>
                                </div>

                                <div class="col-xs-4 text-right display-table-cell vertical-align-middle float-none padding-right-0">
                                    <div class="scene-icon font-size-xl line-height-none">
                                        💡
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

@include('widgets::Scenes.resources.views.modals.settings')

