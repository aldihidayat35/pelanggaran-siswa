@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="w-100">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-gray-900 fw-bold fs-2 mb-1.5" style="font-family: 'Playfair Display', serif;">Selamat datang kembali</h2>
        <p class="text-gray-500 fw-semibold fs-7 mb-0">Masukkan kredensial Anda untuk melanjutkan</p>
    </div>

    @if($errors->any())
        <div class="alert alert-light-danger d-flex align-items-center p-4 mb-6 rounded-3 border border-danger border-opacity-15">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column text-danger fw-semibold fs-8">
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <form class="form w-100" method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Input NIP / Username -->
        <div class="fv-row mb-5">
            <label class="form-label fs-7 fw-semibold text-gray-800 mb-1.5">NIP / Username</label>
            <div class="form-input-wrapper mb-0">
                <i class="ki-duotone ki-user fs-2 text-gray-400"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" placeholder="Masukkan NIP atau username" name="email" autocomplete="off"
                    class="@error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required autofocus
                    style="border-radius: 8px; font-size: 14px; border-color: #e2e8f0; background-color: #ffffff;"/>
            </div>
        </div>

        <!-- Input Kata Sandi -->
        <div class="fv-row mb-6">
            <label class="form-label fs-7 fw-semibold text-gray-800 mb-1.5">Kata Sandi</label>
            <div class="form-input-wrapper mb-2">
                <i class="ki-duotone ki-lock-2 fs-2 text-gray-400"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                <input type="password" id="password-input" placeholder="Masukkan kata sandi" name="password" autocomplete="off"
                    class="@error('password') is-invalid @enderror" required
                    style="border-radius: 8px; font-size: 14px; border-color: #e2e8f0; background-color: #ffffff;"/>
                <i class="ki-duotone ki-eye-slash fs-2 text-gray-400 position-absolute" id="toggle-password-btn" style="right: 1.15rem; left: auto; cursor: pointer;">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
            </div>
            <div class="d-flex justify-content-end">
                <a href="#" class="fs-8 fw-semibold text-primary text-decoration-none" style="color: #0b57d0 !important;">Lupa kata sandi?</a>
            </div>
        </div>

        <!-- Separator -->
        <div class="d-flex align-items-center mb-6">
            <div class="flex-grow-1" style="height: 1px; background-color: #e2e8f0;"></div>
            <span class="px-4 text-muted fs-8">atau</span>
            <div class="flex-grow-1" style="height: 1px; background-color: #e2e8f0;"></div>
        </div>

        <!-- Face Authentication Box -->
        <div class="p-5 mb-6 rounded-3" style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
            <h5 class="fs-7 fw-bold text-gray-800 mb-0.5">Autentikasi Wajah</h5>
            <p class="text-gray-500 fs-8 mb-4">Posisikan wajah Anda di tengah kamera</p>

            <div class="camera-feed my-4">
                <!-- Corner brackets -->
                <div class="scan-bracket top-left"></div>
                <div class="scan-bracket top-right"></div>
                <div class="scan-bracket bottom-left"></div>
                <div class="scan-bracket bottom-right"></div>

                <!-- Face landmark connections (detailed SVG) -->
                <svg width="110" height="110" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: rgba(59, 130, 246, 0.75); position: absolute; top: calc(50% - 55px); left: calc(50% - 55px); z-index: 2;">
                    <!-- Central vertical axis -->
                    <line x1="50" y1="10" x2="50" y2="90" stroke="rgba(59, 130, 246, 0.35)" stroke-width="0.75" stroke-dasharray="2 2"/>
                    <!-- Eye horizontal axis -->
                    <line x1="20" y1="42" x2="80" y2="42" stroke="rgba(59, 130, 246, 0.35)" stroke-width="0.75" stroke-dasharray="2 2"/>
                    
                    <!-- Landmark connections (fine mesh) -->
                    <polygon points="50,20 38,30 50,34" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="50,20 62,30 50,34" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="38,30 26,35 36,42" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="62,30 74,35 64,42" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="38,30 50,34 36,42" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="62,30 50,34 64,42" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="36,42 50,34 50,52" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="64,42 50,34 50,52" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="36,42 50,52 38,62" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="64,42 50,52 62,62" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="50,52 38,62 50,68" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="50,52 62,62 50,68" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="38,62 28,55 36,42" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="62,62 72,55 64,42" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="38,62 50,68 50,78" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="62,62 50,68 50,78" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="38,62 28,70 50,78" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="62,62 72,70 50,78" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="28,70 50,78 50,88" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    <polygon points="72,70 50,78 50,88" stroke="currentColor" stroke-width="0.5" fill="none"/>
                    
                    <!-- Landmark points (dots) -->
                    <circle cx="50" cy="20" r="1.5" fill="#3b82f6"/>
                    <circle cx="38" cy="30" r="1.5" fill="#3b82f6"/>
                    <circle cx="62" cy="30" r="1.5" fill="#3b82f6"/>
                    <circle cx="50" cy="34" r="1.5" fill="#3b82f6"/>
                    <circle cx="26" cy="35" r="1.5" fill="#3b82f6"/>
                    <circle cx="74" cy="35" r="1.5" fill="#3b82f6"/>
                    <circle cx="36" cy="42" r="2" fill="#3b82f6"/>
                    <circle cx="64" cy="42" r="2" fill="#3b82f6"/>
                    <circle cx="28" cy="55" r="1.5" fill="#3b82f6"/>
                    <circle cx="72" cy="55" r="1.5" fill="#3b82f6"/>
                    <circle cx="50" cy="52" r="2" fill="#3b82f6"/>
                    <circle cx="38" cy="62" r="1.5" fill="#3b82f6"/>
                    <circle cx="62" cy="62" r="1.5" fill="#3b82f6"/>
                    <circle cx="50" cy="68" r="1.5" fill="#3b82f6"/>
                    <circle cx="28" cy="70" r="1.5" fill="#3b82f6"/>
                    <circle cx="72" cy="70" r="1.5" fill="#3b82f6"/>
                    <circle cx="50" cy="78" r="1.5" fill="#3b82f6"/>
                    <circle cx="50" cy="88" r="1.5" fill="#3b82f6"/>
                </svg>

                <!-- Scanning line animation -->
                <div style="position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(to right, rgba(59,130,246,0), rgba(59,130,246,0.85), rgba(59,130,246,0)); top: 15%; animation: scan 3.5s infinite linear; pointer-events: none; z-index: 3;"></div>

                <!-- Status badge pill -->
                <div style="position: absolute; bottom: 12px; left: 12px; z-index: 4;" class="badge-scan">
                    <span class="dot"></span>Siap memindai
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-column gap-3">
            <button type="submit" class="btn text-white fw-semibold fs-7" 
                style="background-color: #000000; border-radius: 8px; padding: 12px 20px; border: none;">
                Masuk
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-password-btn');
        const passwordInput = document.getElementById('password-input');
        
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleBtn.classList.remove('ki-eye-slash');
                    toggleBtn.classList.add('ki-eye');
                } else {
                    passwordInput.type = 'password';
                    toggleBtn.classList.remove('ki-eye');
                    toggleBtn.classList.add('ki-eye-slash');
                }
            });
        }
    });
</script>
@endsection
