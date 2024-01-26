<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
    header('Permissions-Policy: interest-cohort=()');
    ?>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Diadem Optimization</title>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <main class="container-fluid">
            <div class="row">
                <div class="col"></div>
                <div class="main-content col-lg-8 p-4" id="content">
                    <div>
                        <div class="text-center mb-4">
                            <a href="{{ url('/') }}"
                                class="btn {{ url()->current() == url('/') ? 'btn-success' : 'btn-primary' }} py-0 my-1 my-md-0">Index</a>
                            <a href="{{ url('leveling') }}"
                                class="btn {{ url()->current() == url('leveling') ? 'btn-success' : 'btn-primary' }} py-0 my-1 my-md-0">Leveling
                                Calculator</a>
                            <a href="{{ url('diadem') }}"
                                class="btn {{ url()->current() == url('diadem') ? 'btn-success' : 'btn-primary' }} py-0 my-1 my-md-0">Diadem
                                Optimization</a>
                        </div>
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
    </div>
</body>

</html>
