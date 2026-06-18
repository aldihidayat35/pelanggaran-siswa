<div class="card card-flush">
    <div class="card-header">
        <h3 class="card-title">Daftar Pelanggaran Siswa</h3>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="laporan-pelanggaran-table">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Pelanggaran</th>
                        <th>Kategori</th>
                        <th>Tingkat</th>
                        <th>Poin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="9" class="text-end">Total Poin:</td>
                        <td id="footer-total-poin">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('vendor-css')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
@endpush

@push('vendor-js')
<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
@endpush

@push('custom-js')
<script>
    $(document).ready(function() {
        var table = $('#laporan-pelanggaran-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pelanggaran-siswa.laporan.index') }}",
                data: function(d) {
                    d.tipe = 'pelanggaran-siswa';
                    d.tanggal_mulai = $('input[name="tanggal_mulai"]').val();
                    d.tanggal_selesai = $('input[name="tanggal_selesai"]').val();
                    d.siswa_id = $('select[name="siswa_id"]').val();
                    d.kategori_id = $('select[name="kategori_id"]').val();
                    d.pelanggaran_id = $('select[name="pelanggaran_id"]').val();
                    d.kelas = $('input[name="kelas"]').val();
                    d.jurusan = $('input[name="jurusan"]').val();
                    d.status_penanganan = $('select[name="status_penanganan"]').val();
                    d.status_siswa = $('select[name="status_siswa"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal_pelanggaran', name: 'tanggal_pelanggaran' },
                { data: 'nis', name: 'siswa.nis' },
                { data: 'nama', name: 'siswa.nama' },
                { data: 'kelas', name: 'siswa.kelas' },
                { data: 'jurusan', name: 'siswa.jurusan' },
                { data: 'pelanggaran', name: 'pelanggaran.nama_pelanggaran' },
                { data: 'kategori', name: 'kategori', orderable: false, searchable: false },
                { data: 'tingkat', name: 'pelanggaran.tingkat' },
                { data: 'poin', name: 'poin' },
                { data: 'status', name: 'status_penanganan' }
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
                if (typeof KTMenu !== 'undefined') {
                    KTMenu.createInstances();
                }
            },
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                var json = api.ajax.json();
                var total = json ? json.totalPoin : 0;
                $('#footer-total-poin').text(total);
            }
        });
    });
</script>
@endpush
