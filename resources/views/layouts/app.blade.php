<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
    header('Permissions-Policy: interest-cohort=()');
    ?>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        FFXIV Tools
        @hasSection('title')
            @yield('title')
        @endif
    </title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="FFXIV Tools @if (View::hasSection('title')) ・@yield('title') @endif">
    <meta name="description" content="@if (View::hasSection('meta-desc')) @yield('meta-desc') @else A selection of browser utilities for Final Fantasy XIV. @endif">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url', 'http://localhost') }}">
    <meta property="og:site_name" content="FFXIV Tools" />
    @if (View::hasSection('title'))
        <meta property="og:title" content="
            @yield('title')
        ">
    @endif
    <meta property="og:description" content="@if (View::hasSection('meta-desc')) @yield('meta-desc') @else A selection of browser utilities for Final Fantasy XIV. @endif">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @include('layouts._theme_switch_js')

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.png') }}">
</head>

<body>
    <div id="app">
        <main class="container-fluid">
            <div class="row">
                <div class="col"></div>
                <div class="main-content col-lg-8 p-4" id="content">
                    <div>
                        @include('layouts._nav')
                        @include('flash::message')
                        @yield('content')
                    </div>

                    <div class="site-footer mt-4" id="footer">
                        @include('layouts._footer')
                    </div>
                </div>
                <div class="col"></div>
            </div>
        </main>

        <div class="modal fade" id="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0"></span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        @yield('scripts')
        <script type="module">
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        </script>
    </div>
</body>

</html>
