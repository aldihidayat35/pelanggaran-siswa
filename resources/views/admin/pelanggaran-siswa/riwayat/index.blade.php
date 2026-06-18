@extends('layouts.app')

@section('title', 'Riwayat Pelanggaran')
@section('page-title', 'Riwayat Pelanggaran')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Riwayat Pelanggaran</li>
</ul>
@endsection

@push('vendor-css')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
@endpush

@push('vendor-js')
<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
@endpush

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form id="search-form" class="d-flex align-items-center">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                    <input type="text" id="search-filter" class="form-control form-control-solid w-250px ps-13"
                        placeholder="Cari nama siswa / NIS..."/>
                </div>
                <select id="siswa-filter" class="form-select form-select-solid w-200px ms-3">
                    <option value="">Semua Siswa</option>
                    @foreach($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }})</option>
                    @endforeach
                </select>
                <select id="status-penanganan-filter" class="form-select form-select-solid w-200px ms-3">
                    <option value="">Semua Status</option>
                    <option value="Belum Diproses">Belum Diproses</option>
                    <option value="Diproses">Diproses</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </form>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('pelanggaran-siswa.riwayat.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i> Catat Pelanggaran
            </a>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="riwayat-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">#</th>
                        <th class="min-w-100px">Tanggal</th>
                        <th class="min-w-200px">Siswa</th>
                        <th class="min-w-150px">Pelanggaran</th>
                        <th class="min-w-150px">Kategori</th>
                        <th class="min-w-100px">Poin</th>
                        <th class="min-w-150px">Status</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('custom-js')
<script>
    $(document).ready(function() {
        var table = $('#riwayat-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pelanggaran-siswa.riwayat.index') }}",
                data: function(d) {
                    d.siswa_id = $('#siswa-filter').val();
                    d.status_penanganan = $('#status-penanganan-filter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal_pelanggaran', name: 'tanggal_pelanggaran' },
                { data: 'siswa', name: 'siswa_id' },
                { data: 'pelanggaran', name: 'pelanggaran_id' },
                { data: 'kategori', name: 'kategori', orderable: false, searchable: false },
                { data: 'poin', name: 'poin' },
                { data: 'status', name: 'status_penanganan' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            drawCallback: function(settings) {
                KTMenu.createInstances();
            }
        });

        // Trigger filters on change
        $('#siswa-filter, #status-penanganan-filter').on('change', function() {
            table.draw();
        });

        // Custom search input binding
        $('#search-filter').on('keyup', function() {
            table.search($(this).val()).draw();
        });
        
        // Prevent form submit on enter
        $('#search-form').on('submit', function(e) {
            e.preventDefault();
        });
    });
</script>
@endpush
