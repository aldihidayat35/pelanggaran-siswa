@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.users.index') }}" class="text-muted text-hover-primary">Manajemen User</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Tambah User</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Tambah User Baru</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Avatar</label>
                <div class="col-lg-8">
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url('assets/media/avatars/blank.png')"></div>
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Ganti avatar">
                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg"/>
                            <input type="hidden" name="avatar_remove"/>
                        </label>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Batalkan">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Hapus avatar">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                    </div>
                    @error('avatar')
                        <div class="text-danger mt-2 fs-7">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Lengkap</label>
                <div class="col-lg-8">
                    <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror"
                        placeholder="Nama lengkap" value="{{ old('name') }}"/>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Email</label>
                <div class="col-lg-8">
                    <input type="email" name="email" class="form-control form-control-lg form-control-solid @error('email') is-invalid @enderror"
                        placeholder="Email" value="{{ old('email') }}"/>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Password</label>
                <div class="col-lg-8">
                    <input type="password" name="password" class="form-control form-control-lg form-control-solid @error('password') is-invalid @enderror"
                        placeholder="Password"/>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Konfirmasi Password</label>
                <div class="col-lg-8">
                    <input type="password" name="password_confirmation" class="form-control form-control-lg form-control-solid"
                        placeholder="Konfirmasi password"/>
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Role</label>
                <div class="col-lg-8">
                    <select name="role" class="form-select form-select-solid form-select-lg @error('role') is-invalid @enderror">
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Status Aktif</label>
                <div class="col-lg-8 d-flex align-items-center">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked/>
                        <label class="form-check-label text-muted">Aktifkan user ini</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
