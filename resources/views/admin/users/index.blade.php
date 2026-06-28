@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Manajemen</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Daftar User</li>
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
                        placeholder="Cari user..."/>
                </div>
                <select id="role-filter" class="form-select form-select-solid w-150px ms-3">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="user">User</option>
                </select>
                <select id="status-filter" class="form-select form-select-solid w-150px ms-3">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </form>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i> Tambah User
            </a>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="users-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">#</th>
                        <th class="min-w-200px">User</th>
                        <th class="min-w-125px">Role</th>
                        <th class="min-w-125px">Status</th>
                        <th class="min-w-125px">Bergabung</th>
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
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.users.index') }}",
                data: function(d) {
                    d.role = $('#role-filter').val();
                    d.status = $('#status-filter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'user', name: 'name' },
                { data: 'role', name: 'role' },
                { data: 'status', name: 'is_active' },
                { data: 'created_at', name: 'created_at' },
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
        $('#role-filter, #status-filter').on('change', function() {
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
