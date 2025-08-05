@extends('layouts.app')

@section('title', 'HomeHub')

@section('content')
<div>
    <!-- Message d'état -->
    <div id="status-message" class="alert hidden" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 500; min-width: 300px;">
        <span id="status-text"></span>
    </div>

    <!-- Grille des widgets avec masonry -->
    <div id="widgets-container" class="row">
        <!-- Colonnes dynamiques pour masonry -->
        <div id="column-left" class="col-md-6 col-sm-12 widgets-column">
        </div>
        <div id="column-right" class="col-md-6 col-sm-12 widgets-column">
        </div>

        <!-- Widgets automatiques (générés dynamiquement) -->
        @renderAllWidgets

    </div>
</div>
@endsection

@push('scripts')

@if(isset($widgetAssets))
    @foreach($widgetAssets['css'] ?? [] as $cssFile)
        <link rel="stylesheet" href="{{ asset($cssFile) }}">
    @endforeach

    @foreach($widgetAssets['js'] ?? [] as $jsFile)
        <script src="{{ asset($jsFile) }}"></script>
    @endforeach
@endif

@endpush
