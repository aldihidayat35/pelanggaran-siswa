@extends('layouts.app')

@section('title', 'Laporan Pelanggaran Siswa')
@section('page-title', 'Laporan Pelanggaran Siswa')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('pelanggaran-siswa.dashboard') }}" class="text-muted text-hover-primary">Beranda</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-300 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-dark">Laporan Pelanggaran Siswa</li>
</ul>
@endsection

@section('content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

        <div class="card card-flush mb-6">
            <div class="card-header">
                <h3 class="card-title">Laporan Pelanggaran Siswa</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    Pilih jenis laporan dan terapkan filter yang tersedia. Setiap laporan mendukung cetak PDF dan export Excel.
                </p>
            </div>
        </div>

        @php
            $tipeOptions = [
                'pelanggaran-siswa' => 'Daftar Pelanggaran Siswa',
                'rekap-bulanan'     => 'Rekap Bulanan',
                'rekap-kategori'    => 'Rekap Per Kategori',
                'rekap-jenis'       => 'Rekap Per Jenis Pelanggaran',
                'rekap-kelas'       => 'Rekap Per Kelas / Jurusan',
                'ranking-siswa'     => 'Ranking Siswa (Poin Tertinggi)',
                'status-penanganan' => 'Rekap Status Penanganan',
            ];
        @endphp

        <form method="GET" action="{{ route('pelanggaran-siswa.laporan.index') }}" class="card card-flush mb-6">
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-4">
                        <label class="form-label">Jenis Laporan</label>
                        <select name="tipe" class="form-select form-select-solid">
                            @foreach($tipeOptions as $key => $label)
                                <option value="{{ $key }}" @selected($tipe === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-solid"
                            value="{{ old('tanggal_mulai', request('tanggal_mulai')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-solid"
                            value="{{ old('tanggal_selesai', request('tanggal_selesai')) }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-6">
                    <a href="{{ route('pelanggaran-siswa.laporan.index') }}" class="btn btn-light">Reset</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-duotone ki-magnifier fs-2"></i>
                        Tampilkan
                    </button>
                </div>
            </div>
        </form>

        @php
            $startDate = request('tanggal_mulai');
            $endDate   = request('tanggal_selesai');
        @endphp

        @include('admin.pelanggaran-siswa.laporan._partials._actions', ['tipe' => $tipe, 'request' => request()])

        @switch($tipe)
            @case('rekap-bulanan')
                @include('admin.pelanggaran-siswa.laporan._partials._report-rekap-bulanan', [
                    'startDate' => $startDate, 'endDate' => $endDate,
                ])
                @break

            @case('rekap-kategori')
                @include('admin.pelanggaran-siswa.laporan._partials._report-rekap-kategori', [
                    'startDate' => $startDate, 'endDate' => $endDate,
                ])
                @break

            @case('rekap-jenis')
                @include('admin.pelanggaran-siswa.laporan._partials._report-rekap-jenis', [
                    'startDate' => $startDate, 'endDate' => $endDate,
                ])
                @break

            @case('rekap-kelas')
                @include('admin.pelanggaran-siswa.laporan._partials._report-rekap-kelas', [
                    'startDate' => $startDate, 'endDate' => $endDate,
                ])
                @break

            @case('ranking-siswa')
                @include('admin.pelanggaran-siswa.laporan._partials._report-ranking-siswa', [
                    'startDate' => $startDate, 'endDate' => $endDate,
                ])
                @break

            @case('status-penanganan')
                @include('admin.pelanggaran-siswa.laporan._partials._report-status-penanganan', [
                    'startDate' => $startDate, 'endDate' => $endDate,
                ])
                @break

            @default
                @include('admin.pelanggaran-siswa.laporan._partials._report-pelanggaran-siswa', [
                    'request' => request(),
                ])
        @endswitch

    </div>
</div>
@endsection
