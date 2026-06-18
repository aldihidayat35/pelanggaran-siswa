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
    
    <style>
        /* Global soft background for admin page content */
        [data-bs-theme="light"] #kt_content {
            background-color: #f8fafc !important;
            background-image: radial-gradient(rgba(15, 23, 42, 0.025) 1.2px, transparent 1.2px) !important;
            background-size: 24px 24px !important;
        }
        [data-bs-theme="dark"] #kt_content {
            background-color: #0b0f19 !important;
            background-image: radial-gradient(rgba(255, 255, 255, 0.015) 1.2px, transparent 1.2px) !important;
            background-size: 24px 24px !important;
        }

        /* Modern Sidebar (Aside) styles */
        .aside {
            background-color: #0b0f19 !important;
            border-right: 1px solid rgba(255, 255, 255, 0.03) !important;
        }
        .header-brand {
            background-color: #0b0f19 !important;
            border-right: 1px solid rgba(255, 255, 255, 0.03) !important;
        }
        .header-brand .text-white {
            color: #ffffff !important;
        }
        
        /* Modernized Sidebar User Profile Widget */
        .aside-toolbar .aside-user {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: 12px;
            margin: 15px 20px;
            padding: 12px 15px !important;
            border-top: none !important;
            display: flex;
            align-items: center;
            justify-content: start !important;
        }
        .aside-toolbar .aside-user-info {
            text-align: left !important;
        }
        
        /* Modern Sidebar Menu List styling */
        .aside-menu .menu-item .menu-link {
            border-radius: 8px !important;
            margin: 3px 15px !important;
            padding-left: 12px !important;
            transition: all 0.25s ease;
        }
        .aside-menu .menu-item .menu-link.active {
            background-color: rgba(11, 87, 208, 0.08) !important;
            border-left: 3px solid #0b57d0 !important;
            color: #ffffff !important;
        }
        .aside-menu .menu-item .menu-link:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.03) !important;
            color: #ffffff !important;
        }
        .aside-menu .menu-item .menu-link .menu-icon i {
            color: #94a3b8 !important;
            transition: color 0.25s ease;
        }
        .aside-menu .menu-item .menu-link.active .menu-icon i,
        .aside-menu .menu-item .menu-link:hover .menu-icon i {
            color: #3b82f6 !important;
        }
        .aside-menu .menu-heading {
            color: #4b5563 !important;
            font-size: 0.725rem !important;
            font-weight: 700 !important;
            letter-spacing: 1.2px;
            padding-left: 27px !important;
            margin-top: 20px !important;
            margin-bottom: 5px !important;
            text-transform: uppercase;
        }
        .aside-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.03) !important;
            padding: 15px 20px !important;
        }
        .aside-footer .btn-danger {
            background-color: rgba(239, 68, 68, 0.1) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.15) !important;
            transition: all 0.25s ease;
        }
        .aside-footer .btn-danger:hover {
            background-color: #ef4444 !important;
            color: #ffffff !important;
        }

        /* Minimized aside styling */
        body[data-kt-aside-minimize="on"] .aside-toolbar .aside-user {
            margin: 15px 10px;
            padding: 10px !important;
            justify-content: center !important;
        }
        body[data-kt-aside-minimize="on"] .aside-toolbar .aside-user-info {
            display: none !important;
        }
        body[data-kt-aside-minimize="on"] .aside-menu .menu-item .menu-link {
            margin: 3px 5px !important;
            padding-left: 0 !important;
            justify-content: center !important;
        }
    </style>
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
