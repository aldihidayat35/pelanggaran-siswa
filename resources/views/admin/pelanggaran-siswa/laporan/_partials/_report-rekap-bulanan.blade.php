@php
    $data = app(\App\Http\Controllers\Admin\PelanggaranSiswa\LaporanPelanggaranController::class)
        ->buildRekapBulanan($startDate, $endDate);
@endphp

<div class="card card-flush">
    <div class="card-header">
        <h3 class="card-title">Rekap Bulanan</h3>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th>#</th>
                        <th>Periode (Bulan)</th>
                        <th>Jumlah Pelanggaran</th>
                        <th>Jumlah Siswa Terlibat</th>
                        <th>Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->periode . '-01')->translatedFormat('F Y') }}</td>
                        <td>{{ $row->jumlah_pelanggaran }}</td>
                        <td>{{ $row->jumlah_siswa }}</td>
                        <td>{{ $row->total_poin }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-10">Tidak ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
