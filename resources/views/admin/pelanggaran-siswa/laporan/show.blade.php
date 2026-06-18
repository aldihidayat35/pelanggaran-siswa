@extends('layouts.app')

@section('title', 'Laporan Pelanggaran Siswa')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">
    <div>
        <h1 class="fs-2 fw-semibold mb-1">Laporan Pelanggaran Siswa</h1>
        <p class="text-muted mb-0">Cetak dan unduh laporan pelanggaran siswa berdasarkan periode dan filter tertentu.</p>
    </div>
</div>

@include('admin.pelanggaran-siswa.partials._filter-form', [
    'action'    => route('pelanggaran-siswa.laporan.index'),
    'tipe'      => $tipe,
    'tipeOptions' => $tipeOptions,
    'filterOptions' => $filterOptions,
    'request'   => $request,
    'showTipe'  => true,
    'showTanggalMulai' => true,
    'showTanggalSelesai' => true,
    'showSiswa' => $tipe === 'pelanggaran-siswa',
    'showKategori' => in_array($tipe, ['pelanggaran-siswa', 'rekap-kategori']),
    'showPelanggaran' => in_array($tipe, ['pelanggaran-siswa', 'rekap-jenis']),
    'showKelas' => in_array($tipe, ['pelanggaran-siswa', 'rekap-kelas', 'ranking-siswa']),
    'showJurusan' => in_array($tipe, ['pelanggaran-siswa', 'rekap-kelas', 'ranking-siswa']),
    'showStatusPenanganan' => in_array($tipe, ['pelanggaran-siswa', 'status-penanganan']),
    'showStatusSiswa' => in_array($tipe, ['pelanggaran-siswa', 'rekap-bulanan', 'rekap-kategori', 'rekap-jenis', 'rekap-kelas', 'ranking-siswa', 'status-penanganan']),
])

@include('admin.pelanggaran-siswa.laporan._partials._actions', [
    'tipe' => $tipe,
    'request' => $request,
])

@include('admin.pelanggaran-siswa.laporan._partials._report-' . $tipe, [
    'tipe' => $tipe,
    'request' => $request,
    'startDate' => $startDate,
    'endDate' => $endDate,
])

@if(request()->anyFilled(['tanggal_mulai','tanggal_selesai','siswa_id','pelanggaran_id','kategori_id','kelas','jurusan','status_penanganan','tingkat','status_siswa']))
<div class="alert alert-light-info d-flex align-items-center mt-6">
    <i class="ki-duotone ki-information-5 fs-2x me-3 text-info"></i>
    <div>
        <strong>Filter Aktif.</strong>
        Hasil di bawah sudah disaring sesuai kombinasi filter di atas. Kosongkan filter untuk melihat seluruh data.
    </div>
</div>
@endif
@endsection
