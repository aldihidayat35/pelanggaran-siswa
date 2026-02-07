@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')
@section('page-title', 'Pengaturan Aplikasi')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Pengaturan</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Data Aplikasi</li>
</ul>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @foreach($settings as $group => $items)
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <div class="card-title">
                <h2 class="fw-bold">{{ ucfirst($group) }}</h2>
            </div>
        </div>
        <div class="card-body">
            @foreach($items as $setting)
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ $setting->label }}</label>
                <div class="col-lg-8">
                    @if($setting->type === 'text')
                        <input type="text" name="settings[{{ $setting->key }}]"
                            class="form-control form-control-lg form-control-solid"
                            value="{{ old('settings.' . $setting->key, $setting->value) }}"/>

                    @elseif($setting->type === 'textarea')
                        <textarea name="settings[{{ $setting->key }}]"
                            class="form-control form-control-lg form-control-solid"
                            rows="3">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>

                    @elseif($setting->type === 'image')
                        <div class="d-flex align-items-center">
                            @if($setting->value)
                                <div class="me-5">
                                    <img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->label }}"
                                        class="mw-150px mh-75px rounded"/>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <input type="file" name="settings[{{ $setting->key }}]"
                                    class="form-control form-control-lg form-control-solid"
                                    accept=".png,.jpg,.jpeg,.svg,.ico"/>
                                <div class="form-text text-muted">Format: JPG, PNG, SVG, ICO. Max: 2MB</div>
                            </div>
                        </div>

                    @elseif($setting->type === 'boolean')
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="settings[{{ $setting->key }}]"
                                value="1" {{ $setting->value ? 'checked' : '' }}/>
                        </div>

                    @elseif($setting->type === 'color')
                        <input type="color" name="settings[{{ $setting->key }}]"
                            class="form-control form-control-color form-control-solid"
                            value="{{ old('settings.' . $setting->key, $setting->value) }}"/>

                    @else
                        <input type="text" name="settings[{{ $setting->key }}]"
                            class="form-control form-control-lg form-control-solid"
                            value="{{ old('settings.' . $setting->key, $setting->value) }}"/>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            <i class="ki-duotone ki-check fs-2"></i> Simpan Pengaturan
        </button>
    </div>
</form>
@endsection
