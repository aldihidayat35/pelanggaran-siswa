<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <base href="{{ url('/') }}/"/>
    <title>@yield('title', 'Login') - {{ app_setting('app_name', config('app.name')) }}</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <link rel="shortcut icon" href="{{ app_setting('favicon', 'assets/media/logos/favicon.ico') }}"/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css"/>
    
    <style>
        body.auth-custom-bg {
            background-image: linear-gradient(rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1)), url('assets/media/misc/school_bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        /* Soft blur/monochrome adjustment overlay */
        body.auth-custom-bg::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(241, 245, 249, 0.35);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            pointer-events: none;
            z-index: 1;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 1050px;
            overflow: hidden;
            z-index: 10;
            border: 1px solid #e2e8f0;
        }
        .left-panel {
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        .right-panel {
            background-color: #ffffff;
        }
        .badge-scan {
            background: #ffffff;
            border-radius: 50px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 600;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .badge-scan .dot {
            width: 8px;
            height: 8px;
            background-color: #0b57d0;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-block;
        }
        .camera-feed {
            background-image: url('assets/media/misc/face_mockup.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 8px;
            height: 210px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
        }
        /* Face Scanner corner lines */
        .scan-bracket {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #3b82f6;
            border-style: solid;
            pointer-events: none;
            opacity: 0.85;
        }
        .scan-bracket.top-left {
            top: 40px; left: calc(50% - 60px);
            border-width: 2.5px 0 0 2.5px;
        }
        .scan-bracket.top-right {
            top: 40px; right: calc(50% - 60px);
            border-width: 2.5px 2.5px 0 0;
        }
        .scan-bracket.bottom-left {
            bottom: 40px; left: calc(50% - 60px);
            border-width: 0 0 2.5px 2.5px;
        }
        .scan-bracket.bottom-right {
            bottom: 40px; right: calc(50% - 60px);
            border-width: 0 2.5px 2.5px 0;
        }
        @keyframes scan {
            0% { top: 15%; }
            50% { top: 85%; }
            100% { top: 15%; }
        }
        .form-input-wrapper {
            position: relative;
        }
        .form-input-wrapper input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.85rem;
            border: 1.5px solid #e2e8f0;
            background-color: #ffffff;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #1e293b;
            transition: all 0.2s ease;
        }
        .form-input-wrapper input:focus {
            outline: none;
            border-color: #0b57d0;
            box-shadow: 0 0 0 4px rgba(11, 87, 208, 0.08);
        }
        .form-input-wrapper i {
            position: absolute;
            left: 1.15rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
            pointer-events: none;
        }
    </style>
</head>
<body id="kt_body" class="auth-custom-bg">
    @include('layouts.partials._theme-mode')

    <div class="d-flex flex-column flex-root w-100 align-items-center justify-content-center min-vh-100 p-4">
        <!-- Relative container to wrap dots and the main card -->
        <div class="position-relative w-100" style="max-width: 1050px; z-index: 10;">
            <!-- Dots decoration behind the card at top-left -->
            <div class="d-none d-lg-block" style="position: absolute; top: -35px; left: -35px; width: 96px; height: 140px; opacity: 0.15; z-index: 0; background-image: radial-gradient(#000000 2px, transparent 2px); background-size: 14px 14px; pointer-events: none;"></div>

            <div class="login-card container-fluid p-0 position-relative" style="z-index: 1;">
                <div class="row g-0">
                    <!-- Left Column (Branding) -->
                    <div class="col-lg-6 left-panel d-none d-lg-flex flex-column justify-content-between p-12 p-xl-15" style="min-height: 680px;">
                        <!-- Subtle radial gradient background pattern -->
                        <div style="position: absolute; right: 0; bottom: 0; top: 0; width: 100%; background: radial-gradient(circle at 100% 50%, rgba(11, 87, 208, 0.04) 0%, rgba(255, 255, 255, 0) 70%); pointer-events: none;"></div>

                        <!-- School Logo & Header -->
                        <div class="d-flex align-items-center" style="position: relative; z-index: 2;">
                            <div class="me-4 text-black">
                                @if(app_setting('app_logo'))
                                    <img src="{{ asset('storage/' . app_setting('app_logo')) }}" alt="Logo" class="mh-50px mw-50px rounded"/>
                                @else
                                    <svg width="40" height="46" viewBox="0 0 40 46" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #000000;">
                                        <path d="M20 2C31 2 37 5 37 18C37 31 28 41 20 44C12 41 3 31 3 18C3 5 9 2 20 2Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 9L22.2 13.5L27.1 14.2L23.6 17.6L24.4 22.5L20 20.2L15.6 22.5L16.4 17.6L12.9 14.2L17.8 13.5L20 9Z" fill="currentColor"/>
                                        <path d="M12 32C15 32 20 29 20 29C20 29 25 32 28 32" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 35C15 35 20 32 20 32C20 32 25 35 28 35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 29V35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-gray-900 fw-bold m-0" style="font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.5px; font-size: 14px;">{{ app_setting('app_name', 'IDENTITAS SEKOLAH') }}</h4>
                                <span class="text-muted fw-semibold fs-8 text-uppercase" style="letter-spacing: 0.5px;">{{ app_setting('app_description', 'SLOGAN SEKOLAH') }}</span>
                            </div>
                        </div>

                        <!-- Main Title & Desc -->
                        <div class="my-auto py-10" style="max-width: 440px; position: relative; z-index: 2;">
                            <h1 class="text-gray-900 fw-bold mb-3" style="font-family: 'Playfair Display', serif; font-size: 3.5rem; line-height: 1.1;">
                                Sistem<br>Pelanggaran<br><span style="color: #0b57d0;">Siswa</span>
                            </h1>
                            <div class="mb-8" style="width: 76px; height: 4px; background-color: #0b57d0; border-radius: 2px;"></div>
                            <p class="text-gray-600 fs-7" style="line-height: 1.6; max-width: 380px;">
                                Sistem terintegrasi berbasis pengenalan wajah untuk mencatat dan mengelola pelanggaran siswa secara akurat, aman, dan efisien.
                            </p>
                        </div>

                        <!-- Bottom Features List -->
                        <div class="d-flex align-items-center gap-8" style="position: relative; z-index: 2;">
                            <div class="d-flex flex-column align-items-center text-center" style="width: 110px;">
                                <div class="rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; border: 1.5px solid #e2e8f0; background: #ffffff;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                        <path d="m9 11 2 2 4-4"/>
                                    </svg>
                                </div>
                                <span class="fs-8 fw-semibold text-gray-600">Aman & Terpercaya</span>
                            </div>
                            <div class="d-flex flex-column align-items-center text-center" style="width: 110px;">
                                <div class="rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; border: 1.5px solid #e2e8f0; background: #ffffff;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 7V5a2 2 0 0 1 2-2h2m10 0h2a2 2 0 0 1 2 2v2m0 10v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </div>
                                <span class="fs-8 fw-semibold text-gray-600">Pengenalan Wajah</span>
                            </div>
                            <div class="d-flex flex-column align-items-center text-center" style="width: 110px;">
                                <div class="rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; border: 1.5px solid #e2e8f0; background: #ffffff;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="20" x2="18" y2="10"/>
                                        <line x1="12" y1="20" x2="12" y2="4"/>
                                        <line x1="6" y1="20" x2="6" y2="14"/>
                                    </svg>
                                </div>
                                <span class="fs-8 fw-semibold text-gray-600">Data Akurat</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Login Form) -->
                    <div class="col-lg-6 right-panel p-10 p-lg-12 d-flex align-items-center justify-content-center">
                        <div class="w-100" style="max-width: 420px;">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="position-absolute bottom-0 w-100 text-center py-4 text-gray-600 fs-8 z-index-2" style="background-color: transparent;">
        &copy; {{ date('Y') }} {{ app_setting('app_name', 'Identitas Sekolah') }}. Hak cipta dilindungi.
    </div>

    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
</body>
</html>
