@extends('layouts.app')

@section('title', 'Dashboard Pelanggaran Siswa')
@section('page-title', 'Dashboard Pelanggaran Siswa')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Dashboard</li>
</ul>
@endsection

@section('content')
<!--begin::Welcome Banner-->
<div class="card border-0 mb-8" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 12px; box-shadow: 0 4px 20px rgba(30, 60, 114, 0.15);">
    <div class="card-body p-8 p-lg-12 d-flex flex-column flex-md-row align-items-center justify-content-between position-relative overflow-hidden">
        <!-- Background decoration -->
        <div class="position-absolute top-0 end-0 translate-middle-y opacity-10" style="font-size: 200px; color: #fff; pointer-events: none; transform: rotate(15deg);">🛡️</div>
        
        <div class="mb-6 mb-md-0 z-index-2 text-center text-md-start">
            <h1 class="text-white fs-2tx fw-bold mb-3">Selamat Datang Kembali, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-white opacity-75 fs-5 mb-0">Sistem Informasi Pelanggaran Siswa. Pantau, catat, dan kelola kedisiplinan hari ini.</p>
        </div>
        <div class="d-flex flex-column align-items-center bg-white bg-opacity-10 rounded px-6 py-4 z-index-2 min-w-150px">
            <span class="fs-4 text-white opacity-75 fw-semibold">{{ now()->translatedFormat('l') }}</span>
            <span class="fs-2hx text-white fw-bold mt-1">{{ now()->translatedFormat('d M Y') }}</span>
        </div>
    </div>
</div>
<!--end::Welcome Banner-->

<!--begin::Stats Row-->
<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3">
        <div class="card bg-light-primary border-0 card-xl-stretch">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-gray-900 fw-bold fs-1 mb-1">{{ $totalSiswa }}</div>
                        <div class="fw-bold text-primary fs-6">Total Siswa</div>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="ki-duotone ki-people text-primary fs-2hx"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    </div>
                </div>
                <div class="progress h-6px bg-primary bg-opacity-10 mt-4">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card bg-light-success border-0 card-xl-stretch">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-gray-900 fw-bold fs-1 mb-1">{{ $totalJenisPelanggaran }}</div>
                        <div class="fw-bold text-success fs-6">Jenis Pelanggaran</div>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="ki-duotone ki-shield-cross text-success fs-2hx"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="progress h-6px bg-success bg-opacity-10 mt-4">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card bg-light-danger border-0 card-xl-stretch">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-gray-900 fw-bold fs-1 mb-1">{{ $totalPelanggaran }}</div>
                        <div class="fw-bold text-danger fs-6">Riwayat Kasus</div>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="ki-duotone ki-note-2 text-danger fs-2hx"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </div>
                </div>
                <div class="progress h-6px bg-danger bg-opacity-10 mt-4">
                    <div class="progress-bar bg-danger" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card bg-light-warning border-0 card-xl-stretch">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-gray-900 fw-bold fs-1 mb-1">{{ $totalSiswaPernahMelanggar }}</div>
                        <div class="fw-bold text-warning fs-6">Siswa Melanggar</div>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="ki-duotone ki-warning text-warning fs-2hx"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                @php
                    $persentase = $totalSiswa > 0 ? round(($totalSiswaPernahMelanggar / $totalSiswa) * 100) : 0;
                @endphp
                <div class="progress h-6px bg-warning bg-opacity-10 mt-4">
                    <div class="progress-bar bg-warning" style="width: {{ $persentase }}%"></div>
                </div>
                <div class="text-muted fs-8 mt-2">{{ $persentase }}% dari total seluruh siswa</div>
            </div>
        </div>
    </div>
</div>
<!--end::Stats Row-->

<!--begin::Charts Row 1-->
<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-8">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Trend Kasus Pelanggaran Bulanan</span>
                    <span class="text-muted fw-semibold fs-7">Jumlah akumulasi kasus per bulan</span>
                </h3>
            </div>
            <div class="card-body">
                <div id="kt_charts_trend_pelanggaran" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Distribusi Kategori</span>
                    <span class="text-muted fw-semibold fs-7">Rasio kategori pelanggaran</span>
                </h3>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div id="kt_charts_kategori_pelanggaran" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
<!--end::Charts Row 1-->

<!--begin::Charts Row 2-->
<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Tingkat Keparahan (Severity)</span>
                    <span class="text-muted fw-semibold fs-7">Kasus berdasarkan Ringan, Sedang, Berat</span>
                </h3>
            </div>
            <div class="card-body">
                <div id="kt_charts_tingkat_pelanggaran" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Status Penanganan</span>
                    <span class="text-muted fw-semibold fs-7">Status pemrosesan kasus siswa</span>
                </h3>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div id="kt_charts_status_penanganan" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
<!--end::Charts Row 2-->

<!--begin::Data Tables Row-->
<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Top 5 Siswa dengan Poin Tertinggi</span>
                    <span class="text-muted fw-semibold fs-7">Siswa dengan akumulasi poin terbanyak</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-40px">No</th>
                                <th class="min-w-150px">Siswa</th>
                                <th class="min-w-100px">Kelas</th>
                                <th class="min-w-100px text-end">Total Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSiswa as $siswa)
                            <tr>
                                <td><span class="text-gray-900 fw-bold fs-6">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-6">{{ $siswa->nama }}</span>
                                        <span class="text-muted fs-7">NIS: {{ $siswa->nis }}</span>
                                    </div>
                                </td>
                                <td><span class="text-gray-800 fw-semibold d-block fs-7">{{ $siswa->kelas }}</span></td>
                                <td class="text-end">
                                    <span class="badge badge-light-danger fs-7 fw-bold">{{ $siswa->total_poin ?? 0 }} Poin</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-10">Belum ada data pelanggaran</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Riwayat Pelanggaran Terbaru</span>
                    <span class="text-muted fw-semibold fs-7">Kasus yang baru saja dicatat</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-100px">Tanggal</th>
                                <th class="min-w-150px">Siswa</th>
                                <th class="min-w-150px">Pelanggaran</th>
                                <th class="min-w-80px text-end">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatTerbaru as $r)
                            <tr>
                                <td><span class="text-gray-800 fw-semibold d-block fs-7">{{ $r->tanggal_pelanggaran->format('d M Y') }}</span></td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">{{ $r->siswa->nama ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-7">{{ $r->pelanggaran->nama_pelanggaran ?? '-' }}</span>
                                        <span class="text-muted fs-8">{{ $r->pelanggaran->kategori->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-end"><span class="badge badge-light-warning fs-7 fw-bold">{{ $r->poin }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-10">Belum ada riwayat pelanggaran</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Data Tables Row-->

<!--begin::Ranking Table-->
<div class="card card-flush mb-8">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Ranking Siswa Berdasarkan Poin</span>
            <span class="text-muted fw-semibold fs-7">Seluruh siswa dengan akumulasi pelanggaran teratas</span>
        </h3>
    </div>
    <div class="card-body py-3">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-50px">#</th>
                        <th class="min-w-150px">Siswa</th>
                        <th class="min-w-100px">NIS</th>
                        <th class="min-w-100px">Kelas</th>
                        <th class="min-w-100px">Total Poin</th>
                        <th class="min-w-150px">Status Pembinaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rankingSiswa as $siswa)
                    <tr>
                        <td><span class="text-gray-900 fw-bold fs-6">{{ $loop->iteration + ($rankingSiswa->currentPage() - 1) * $rankingSiswa->perPage() }}</span></td>
                        <td><span class="text-gray-900 fw-bold fs-6">{{ $siswa->nama }}</span></td>
                        <td><span class="text-muted fw-semibold d-block fs-7">{{ $siswa->nis }}</span></td>
                        <td><span class="text-muted fw-semibold d-block fs-7">{{ $siswa->kelas }}</span></td>
                        <td>
                            <span class="badge badge-light-{{ ($siswa->total_poin ?? 0) > 50 ? 'danger' : 'primary' }} fs-7 fw-bold">
                                {{ $siswa->total_poin ?? 0 }} Poin
                            </span>
                        </td>
                        <td>
                            @php $status = $siswa->status_pembinaan; @endphp
                            <span class="badge {{ $status['badge'] }} fs-7 fw-semibold">{{ $status['label'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-10">Belum ada data siswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-5">
            {{ $rankingSiswa->links() }}
        </div>
    </div>
</div>
<!--end::Ranking Table-->
@endsection

@push('custom-js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Chart 1: Bulanan Trend
        var trendEl = document.getElementById("kt_charts_trend_pelanggaran");
        if (trendEl) {
            var options = {
                series: [{
                    name: 'Jumlah Kasus',
                    data: @json($chartBulanData)
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#009EF7'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: @json($chartBulanLabels),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 100]
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                }
            };
            var trendChart = new ApexCharts(trendEl, options);
            trendChart.render();
        }

        // Chart 2: Kategori Donut
        var kategoriEl = document.getElementById("kt_charts_kategori_pelanggaran");
        if (kategoriEl) {
            var optionsKategori = {
                series: @json($chartKategoriData),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: @json($chartKategoriLabels),
                colors: ['#009EF7', '#50CD89', '#F1416C', '#F1BC00', '#7239EA', '#47BE7D'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: { width: 200 },
                        legend: { position: 'bottom' }
                    }
                }],
                legend: {
                    position: 'bottom',
                    fontSize: '11px'
                }
            };
            var kategoriChart = new ApexCharts(kategoriEl, optionsKategori);
            kategoriChart.render();
        }

        // Chart 3: Tingkat Pelanggaran Column
        var tingkatEl = document.getElementById("kt_charts_tingkat_pelanggaran");
        if (tingkatEl) {
            var optionsTingkat = {
                series: [{
                    name: 'Jumlah Kasus',
                    data: @json($chartTingkatData)
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: { enabled: false },
                colors: ['#7239EA'],
                xaxis: {
                    categories: @json($chartTingkatLabels),
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                }
            };
            var tingkatChart = new ApexCharts(tingkatEl, optionsTingkat);
            tingkatChart.render();
        }

        // Chart 4: Status Penanganan Donut
        var statusEl = document.getElementById("kt_charts_status_penanganan");
        if (statusEl) {
            var optionsStatus = {
                series: @json($chartStatusData),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: @json($chartStatusLabels),
                colors: ['#F1416C', '#F1BC00', '#50CD89'], // Red for Belum Diproses, Yellow for Diproses, Green for Selesai
                legend: {
                    position: 'bottom',
                    fontSize: '11px'
                }
            };
            var statusChart = new ApexCharts(statusEl, optionsStatus);
            statusChart.render();
        }
    });
</script>
@endpush
