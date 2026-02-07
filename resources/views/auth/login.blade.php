@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<form class="form w-100" method="POST" action="{{ route('login') }}">
    @csrf

    <div class="text-center mb-11">
        <h1 class="text-gray-900 fw-bolder mb-3">Sign In</h1>
        <div class="text-gray-500 fw-semibold fs-6">Masuk ke panel admin</div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-10">
            <div class="d-flex flex-column">
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <div class="fv-row mb-8">
        <input type="email" placeholder="Email" name="email" autocomplete="off"
            class="form-control bg-transparent @error('email') is-invalid @enderror"
            value="{{ old('email') }}" required autofocus/>
    </div>

    <div class="fv-row mb-8">
        <input type="password" placeholder="Password" name="password" autocomplete="off"
            class="form-control bg-transparent @error('password') is-invalid @enderror" required/>
    </div>

    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
        <div>
            <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}/>
                <span class="form-check-label fw-semibold text-gray-700 fs-base ms-1">Ingat Saya</span>
            </label>
        </div>
    </div>

    <div class="d-grid mb-10">
        <button type="submit" class="btn btn-primary">
            <span class="indicator-label">Sign In</span>
        </button>
    </div>
</form>
@endsection
