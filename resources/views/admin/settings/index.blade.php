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

@push('vendor-css')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
@endpush

@push('vendor-js')
<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
@endpush

@section('content')
<div class="card card-custom">
    <div class="card-header card-header-stretch border-bottom border-gray-200">
        <div class="card-title">
            <h3 class="fw-bold m-0 text-gray-800">Pengaturan Sistem</h3>
        </div>
        <div class="card-toolbar">
            <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active text-active-primary fw-bold px-6" data-bs-toggle="tab" href="#kt_tab_general" role="tab">
                        <i class="ki-duotone ki-setting-2 fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                        Umum & Kontak
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-active-primary fw-bold px-6" data-bs-toggle="tab" href="#kt_tab_whatsapp" role="tab" id="wa-tab-link">
                        <i class="ki-duotone ki-whatsapp fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                        Konfigurasi WA API
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card-body bg-light-soft">
        <div class="tab-content" id="settingsTabContent">
            <!-- GENERAL SETTINGS TAB -->
            <div class="tab-pane fade show active" id="kt_tab_general" role="tabpanel">
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @foreach($settings as $group => $items)
                        @if($group !== 'whatsapp')
                        <div class="card card-custom mb-6 shadow-sm border border-gray-200">
                            <div class="card-header border-bottom border-gray-100 min-h-60px">
                                <div class="card-title">
                                    <h3 class="fw-bold fs-4 text-gray-800 m-0">{{ ucwords(str_replace('_', ' ', $group)) }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @foreach($items as $setting)
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ $setting->label }}</label>
                                    <div class="col-lg-8">
                                        @if($setting->type === 'text')
                                            <input type="text" name="settings[{{ $setting->key }}]"
                                                class="form-control form-control-solid"
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"/>

                                        @elseif($setting->type === 'textarea')
                                            <textarea name="settings[{{ $setting->key }}]"
                                                class="form-control form-control-solid"
                                                rows="3">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>

                                        @elseif($setting->type === 'image')
                                            <div class="d-flex align-items-center">
                                                @if($setting->value)
                                                    <div class="me-5 border p-2 bg-white rounded">
                                                        <img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->label }}"
                                                            class="mw-120px mh-60px rounded"/>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <input type="file" name="settings[{{ $setting->key }}]"
                                                        class="form-control form-control-solid"
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
                                                class="form-control form-control-solid"
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"/>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach

                    <div class="d-flex justify-content-end mt-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-2"></i> Simpan Pengaturan Umum
                        </button>
                    </div>
                </form>
            </div>

            <!-- WHATSAPP CONFIGURATION TAB -->
            <div class="tab-pane fade" id="kt_tab_whatsapp" role="tabpanel">
                <!-- Config & Template Update Form -->
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="card card-custom mb-6 shadow-sm border border-gray-200">
                        <div class="card-header border-bottom border-gray-100 min-h-60px">
                            <div class="card-title">
                                <h3 class="fw-bold fs-4 text-gray-800 m-0">Pengaturan WhatsApp API</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-6 mb-6">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-gray-700">Base URL API</label>
                                    <input type="text" name="settings[wa_api_url]" class="form-control form-control-solid"
                                        value="{{ old('settings.wa_api_url', \App\Models\AppSetting::getValue('wa_api_url')) }}" required/>
                                    <div class="form-text text-muted">Contoh: <code>http://jokiin35.space/api</code></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-gray-700">Token / API Key</label>
                                    <input type="text" name="settings[wa_api_token]" class="form-control form-control-solid"
                                        value="{{ old('settings.wa_api_token', \App\Models\AppSetting::getValue('wa_api_token')) }}" required/>
                                    <div class="form-text text-muted">Token/API Key otorisasi Bearer.</div>
                                </div>
                            </div>
                            
                            <div class="row g-6 mb-6">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-gray-700">Session ID</label>
                                    <input type="text" name="settings[wa_session_id]" class="form-control form-control-solid"
                                        value="{{ old('settings.wa_session_id', \App\Models\AppSetting::getValue('wa_session_id')) }}" required/>
                                    <div class="form-text text-muted">ID Sesi WhatsApp.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-gray-700">Nomor Default Pengirim (Optional)</label>
                                    <input type="text" name="settings[wa_sender_number]" class="form-control form-control-solid"
                                        value="{{ old('settings.wa_sender_number', \App\Models\AppSetting::getValue('wa_sender_number')) }}"/>
                                    <div class="form-text text-muted">Contoh: 6287782292990.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-gray-700">Status WhatsApp API</label>
                                    <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                        <input class="form-check-input" type="checkbox" name="settings[wa_status]"
                                            value="1" {{ \App\Models\AppSetting::getValue('wa_status') === '1' ? 'checked' : '' }}/>
                                        <label class="form-check-label ms-3 text-muted">Aktifkan integrasi notifikasi WA</label>
                                    </div>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-8 border-gray-200"></div>

                            <div class="row g-6">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-gray-700">Template Pesan Pelanggaran</label>
                                    <textarea name="settings[wa_violation_template]" class="form-control form-control-solid" rows="8" required id="wa_template_input">{{ old('settings.wa_violation_template', \App\Models\AppSetting::getValue('wa_violation_template')) }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-gray-700">Placeholder yang Tersedia</label>
                                    <div class="bg-light-warning p-4 rounded border border-warning border-dashed">
                                        <p class="fs-7 text-gray-800 mb-3">Klik tombol placeholder di bawah untuk menyisipkan ke dalam template:</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{nama_siswa}">{nama_siswa}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{nis}">{nis}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{kelas}">{kelas}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{nama_pelanggaran}">{nama_pelanggaran}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{poin_pelanggaran}">{poin_pelanggaran}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{total_poin}">{total_poin}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{tanggal_pelanggaran}">{tanggal_pelanggaran}</button>
                                            <button type="button" class="btn btn-xs btn-outline btn-outline-warning btn-active-light-warning text-dark py-1 px-2 btn-placeholder" data-placeholder="{link_riwayat_laporan}">{link_riwayat_laporan}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end py-6 bg-light-soft border-top border-gray-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-duotone ki-check fs-2"></i> Simpan Konfigurasi WA & Template
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Health Check & Test Message Panels -->
                <div class="row g-6 mb-6">
                    <!-- Health Check Panel -->
                    <div class="col-md-6">
                        <div class="card card-custom h-100 shadow-sm border border-gray-200">
                            <div class="card-header border-bottom border-gray-100 min-h-60px">
                                <div class="card-title">
                                    <h3 class="fw-bold fs-5 text-gray-800 m-0">Health Test API</h3>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-between">
                                <p class="text-muted fs-7 mb-4">
                                    Cek konektivitas server WhatsApp API dan periksa apakah sesi WhatsApp Anda saat ini aktif/terhubung.
                                </p>
                                
                                <div id="health-result-container" class="mb-4 d-none">
                                    <div class="p-3 rounded border d-flex align-items-center mb-3" id="health-api-box">
                                        <span class="bullet bullet-dot h-10px w-10px me-3" id="health-api-dot"></span>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold text-gray-800 fs-7 d-block">API Server Connection</span>
                                            <span class="text-muted fs-8" id="health-api-desc">-</span>
                                        </div>
                                    </div>
                                    
                                    <div class="p-3 rounded border d-flex align-items-center" id="health-session-box">
                                        <span class="bullet bullet-dot h-10px w-10px me-3" id="health-session-dot"></span>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold text-gray-800 fs-7 d-block">WhatsApp Session</span>
                                            <span class="text-muted fs-8" id="health-session-desc">-</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <button type="button" class="btn btn-light-info w-100 fw-bold" id="btn-check-health">
                                        <span class="indicator-label">
                                            <i class="ki-duotone ki-heart fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                                            Cek Health API
                                        </span>
                                        <span class="indicator-progress">
                                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Send Message Panel -->
                    <div class="col-md-6">
                        <div class="card card-custom h-100 shadow-sm border border-gray-200">
                            <div class="card-header border-bottom border-gray-100 min-h-60px">
                                <div class="card-title">
                                    <h3 class="fw-bold fs-5 text-gray-800 m-0">Test Send Message</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="test-send-form">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Nomor Tujuan</label>
                                        <input type="text" id="test-to" class="form-control form-control-solid" placeholder="Contoh: 6281234567890" required/>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Isi Pesan</label>
                                        <textarea id="test-message" class="form-control form-control-solid" rows="2" placeholder="Tulis isi pesan uji coba..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-light-primary w-100 fw-bold" id="btn-test-send">
                                        <span class="indicator-label">
                                            <i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                                            Kirim Pesan Uji Coba
                                        </span>
                                        <span class="indicator-progress">
                                            Sending... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Table Panel -->
                <div class="card card-custom shadow-sm border border-gray-200">
                    <div class="card-header border-bottom border-gray-100 min-h-60px">
                        <div class="card-title">
                            <h3 class="fw-bold fs-4 text-gray-800 m-0">Log Pengiriman WhatsApp</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="wa-logs-table" style="width: 100%;">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">#</th>
                                        <th class="min-w-100px">Waktu</th>
                                        <th class="min-w-120px">Siswa</th>
                                        <th class="min-w-120px">Penerima</th>
                                        <th class="min-w-100px">Jenis</th>
                                        <th class="min-w-200px">Pesan</th>
                                        <th class="min-w-80px">Status</th>
                                        <th class="min-w-150px">Respon API</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-js')
<script>
    $(document).ready(function() {
        // Handle placeholder button click
        $('.btn-placeholder').on('click', function() {
            var placeholder = $(this).data('placeholder');
            var txtarea = $('#wa_template_input')[0];
            var start = txtarea.selectionStart;
            var end = txtarea.selectionEnd;
            var text = txtarea.value;
            var before = text.substring(0, start);
            var after = text.substring(end, text.length);
            txtarea.value = before + placeholder + after;
            txtarea.selectionStart = txtarea.selectionEnd = start + placeholder.length;
            txtarea.focus();
        });

        // Initialize DataTable for WhatsApp Logs
        var table = $('#wa-logs-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.settings.whatsapp.logs') }}",
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'siswa', name: 'siswa_id', orderable: false },
                { data: 'to', name: 'to' },
                { data: 'type', name: 'type' },
                { data: 'message', name: 'message' },
                { data: 'status', name: 'status' },
                { data: 'response', name: 'response', orderable: false, searchable: false }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan log yang sesuai",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            order: [[1, 'desc']],
            drawCallback: function(settings) {
                if (typeof KTMenu !== 'undefined') {
                    KTMenu.createInstances();
                }
            }
        });

        // Tab selection memory
        if (window.location.hash === '#whatsapp') {
            $('#wa-tab-link').tab('show');
        }
        
        $('#wa-tab-link').on('shown.bs.tab', function (e) {
            window.location.hash = 'whatsapp';
            table.columns.adjust().draw();
        });

        $('a[href="#kt_tab_general"]').on('shown.bs.tab', function (e) {
            window.location.hash = '';
        });

        // Health Check AJAX
        $('#btn-check-health').on('click', function(e) {
            e.preventDefault();
            var btn = $(this);
            btn.attr('data-kt-indicator', 'on').prop('disabled', true);
            $('#health-result-container').addClass('d-none');

            $.ajax({
                url: "{{ route('admin.settings.whatsapp.health') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    $('#health-result-container').removeClass('d-none');

                    // Set API box
                    var apiBox = $('#health-api-box');
                    var apiDot = $('#health-api-dot');
                    var apiDesc = $('#health-api-desc');
                    if (res.api_status === 'online') {
                        apiBox.removeClass('border-danger bg-light-danger').addClass('border-success bg-light-success');
                        apiDot.removeClass('bg-danger').addClass('bg-success');
                        apiDesc.text('Connected - ' + res.api_details).removeClass('text-danger').addClass('text-success');
                    } else {
                        apiBox.removeClass('border-success bg-light-success').addClass('border-danger bg-light-danger');
                        apiDot.removeClass('bg-success').addClass('bg-danger');
                        apiDesc.text('Offline - ' + res.api_details).removeClass('text-success').addClass('text-danger');
                    }

                    // Set Session box
                    var sessionBox = $('#health-session-box');
                    var sessionDot = $('#health-session-dot');
                    var sessionDesc = $('#health-session-desc');
                    if (res.session_status === 'connected') {
                        sessionBox.removeClass('border-danger bg-light-danger').addClass('border-success bg-light-success');
                        sessionDot.removeClass('bg-danger').addClass('bg-success');
                        sessionDesc.text('Connected - ' + res.session_details).removeClass('text-danger').addClass('text-success');
                    } else {
                        sessionBox.removeClass('border-success bg-light-success').addClass('border-danger bg-light-danger');
                        sessionDot.removeClass('bg-success').addClass('bg-danger');
                        sessionDesc.text('Disconnected - ' + res.session_details).removeClass('text-success').addClass('text-danger');
                    }
                },
                error: function(xhr) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    alert('Error checking health status. Please verify configurations.');
                }
            });
        });

        // Test Send Message AJAX
        $('#test-send-form').on('submit', function(e) {
            e.preventDefault();
            var btn = $('#btn-test-send');
            btn.attr('data-kt-indicator', 'on').prop('disabled', true);

            var to = $('#test-to').val();
            var msg = $('#test-message').val();

            $.ajax({
                url: "{{ route('admin.settings.whatsapp.test-send') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    to: to,
                    message: msg
                },
                success: function(res) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    table.draw();
                    
                    if (res.success) {
                        alert('Pesan uji coba berhasil dikirim!');
                        $('#test-message').val('');
                    } else {
                        alert('Gagal mengirim pesan: ' + res.message);
                    }
                },
                error: function(xhr) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    alert('Terjadi kesalahan pada sistem saat mengirim pesan.');
                }
            });
        });
    });
</script>
@endpush
