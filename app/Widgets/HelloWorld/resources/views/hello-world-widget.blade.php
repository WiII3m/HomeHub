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
                <button class="btn btn-default btn-sm margin-top-4 border-0 font-size-large width-48 padding-0" onclick="openHelloworldSettingsModal()">
                    <span class="font-size-base">⚙️</span>
                </button>
            </div>
        </div>

        @if(empty($devices))
            <div class="text-center padding-vertical-32">
                <div class="text-gray-light">
                    <span class="font-size-2xl display-block margin-bottom-8">🔍</span>
                    Aucun device trouvé
                </div>
            </div>
        @else
            <div class="devices-list row margin-top-16">
                @foreach($devices as $deviceId => $device)
                    <div class="col-sm-6 col-xs-12 margin-bottom-16">
                        <div class="device-card bg-gray-light border-radius-6 padding-16 cursor-pointer hover-gray-lighter active-gray-lightest"
                             data-device-id="{{ $deviceId }}">

                            <div class="margin-bottom-16 line-height-tight display-table width-full">
                                <span class="font-weight-500 text-gray-dark font-size-medium display-table-cell">{{ $device['name'] }}</span>
                                <div class="font-size-small text-gray-light display-table-cell text-right">
                                    <span class="status-point {{ $device['online'] ? 'online' : 'offline' }} border-radius-50 margin-right-4 display-inline-block vertical-align-middle"></span>
                                    <span class="vertical-align-middle">{{ $device['online'] ? 'En ligne' : 'Hors ligne' }}</span>
                                </div>
                            </div>

                            <div class="text-center font-size-small text-gray-light">
                                <span>Device de démonstration</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

{{-- Inclusion du modal de settings pour le tri des devices --}}
@include('widgets::HelloWorld.resources.views.modals.settings')
