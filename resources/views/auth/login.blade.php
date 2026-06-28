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

        <!-- Input Email -->
        <div class="fv-row mb-5">
            <label class="form-label fs-7 fw-semibold text-gray-800 mb-1.5">Email</label>
            <div class="form-input-wrapper mb-0">
                <i class="ki-duotone ki-user fs-2 text-gray-400"><span class="path1"></span><span class="path2"></span></i>
                <input type="email" placeholder="Masukkan email" name="email" autocomplete="username"
                    class="@error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required autofocus
                    style="border-radius: 8px; font-size: 16px; border-color: #e2e8f0; background-color: #ffffff; min-height: 48px;"/>
            </div>
        </div>

        <!-- Input Kata Sandi -->
        <div class="fv-row mb-6">
            <label class="form-label fs-7 fw-semibold text-gray-800 mb-1.5">Kata Sandi</label>
            <div class="form-input-wrapper mb-2">
                <i class="ki-duotone ki-lock-2 fs-2 text-gray-400"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                <input type="password" id="password-input" placeholder="Masukkan kata sandi" name="password" autocomplete="current-password"
                    class="@error('password') is-invalid @enderror" required
                    style="border-radius: 8px; font-size: 16px; border-color: #e2e8f0; background-color: #ffffff; min-height: 48px;"/>
                <i class="ki-duotone ki-eye-slash fs-2 text-gray-400 position-absolute" id="toggle-password-btn" style="right: 1.15rem; left: auto; cursor: pointer;">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
            </div>
            <div class="d-flex justify-content-end">
                <a href="#" class="fs-8 fw-semibold text-primary text-decoration-none" style="color: #0b57d0 !important;">Lupa kata sandi?</a>
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
