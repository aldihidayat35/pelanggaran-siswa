<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <base href="{{ url('/') }}/"/>
    <title>@yield('title', 'Dashboard') - {{ app_setting('app_name', config('app.name')) }}</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <link rel="shortcut icon" href="{{ app_setting('favicon', 'assets/media/logos/favicon.ico') }}"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>
    @stack('vendor-css')
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css"/>
    @stack('custom-css')
</head>
<body id="kt_body" data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on" class="aside-enabled">
    @include('layouts.partials._theme-mode')
    @include('layouts.partials._loader')

    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            @include('layouts.partials._aside')

            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                @include('layouts.partials._header')

                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    @include('layouts.partials._page-title')

                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-fluid">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                                    <div>{{ session('success') }}</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                                    <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
                                    <div>{{ session('error') }}</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @yield('content')
                        </div>
                    </div>
                </div>

                @include('layouts.partials._footer')
            </div>
        </div>
    </div>

    @include('layouts.partials._scrolltop')

    <script>var hostUrl = "assets/";</script>
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
    @stack('vendor-js')
    @stack('custom-js')
</body>
</html>
