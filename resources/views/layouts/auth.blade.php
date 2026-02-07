<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <base href="{{ url('/') }}/"/>
    <title>@yield('title', 'Login') - {{ app_setting('app_name', config('app.name')) }}</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <link rel="shortcut icon" href="{{ app_setting('favicon', 'assets/media/logos/favicon.ico') }}"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css"/>
</head>
<body id="kt_body" class="auth-bg bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
    @include('layouts.partials._theme-mode')

    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">
            <!--begin::Aside-->
            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <div class="d-flex flex-center flex-lg-start flex-column">
                    <a href="{{ url('/') }}" class="mb-7">
                        @if(app_setting('app_logo'))
                            <img alt="Logo" src="{{ asset('storage/' . app_setting('app_logo')) }}" class="h-75px"/>
                        @else
                            <h1 class="text-gray-900 fw-bolder" style="font-size: 3rem;">
                                {{ app_setting('app_name', config('app.name')) }}
                            </h1>
                        @endif
                    </a>
                    <h2 class="text-gray-900 fw-bold m-0" style="font-size: 1.5rem;">
                        {{ app_setting('app_description', 'Sistem Manajemen Aplikasi') }}
                    </h2>
                </div>
            </div>
            <!--end::Aside-->

            <!--begin::Body-->
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
                <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
                    <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">
                        @yield('content')
                    </div>
                    <div class="d-flex flex-stack px-lg-10">
                        <div class="d-flex fw-semibold text-primary fs-base gap-5">
                            <span>&copy; {{ date('Y') }} {{ app_setting('app_name', config('app.name')) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Body-->
        </div>
    </div>

    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
</body>
</html>
