<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HomeHub">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#ffffff">

    <!-- Apple Touch Icons pour écran d'accueil -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icons/apple-touch-icon-180x180.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/icons/apple-touch-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('images/icons/apple-touch-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('images/icons/apple-touch-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('images/icons/apple-touch-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('images/icons/apple-touch-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('images/icons/apple-touch-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('images/icons/apple-touch-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('images/icons/apple-touch-icon-57x57.png') }}">

    <!-- Favicon et icônes génériques -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icons/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icons/favicon.ico') }}">

    <!-- Manifest PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <title>@yield('title', 'HomeHub')</title>

    <!-- Styles CSS -->
    @vite(['resources/css/app.css'])
</head>
<body style="background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; -webkit-font-smoothing: antialiased;">
    <div id="app" style="min-height: 100vh;">
        <!-- Header sticky -->
        <header id="main-header" class="bg-white border-bottom-1 border-gray-light box-shadow-sm z-index-100 width-full">
            <div class="container-fluid">
                <div class="row">
                    @if(request()->routeIs('dashboard'))
                        <div class="col-xs-10 padding-16"">
                            <h1 class="margin-0 font-size-xl font-weight-bold text-black">
                                @yield('header', 'HomeHub')
                            </h1>
                        </div>
                        <div class="col-xs-2 padding-16 text-right ">
                            <button id="settings-btn" class="bg-transparent border-none font-size-xl" onclick="openSettingsModal()" title="Réglages">
                                ⚙️
                            </button>
                        </div>
                    @else
                        <div class="col-xs-12 text-center" style="padding: 16px 15px;">
                            <h1 class="margin-0 font-size-xl font-weight-bold text-black">
                                @yield('header', 'HomeHub')
                            </h1>
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="container-fluid" style="padding: 24px 15px;">
            @yield('content')
        </main>

        @if(request()->routeIs('dashboard'))
            @include('modals.settings')
        @endif
    </div>

    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
        };

    </script>

    <!-- JavaScript Vite Bundle - Legacy compatible -->
    @vite_legacy('resources/js/app.js')

    @stack('scripts')

</body>
</html>
