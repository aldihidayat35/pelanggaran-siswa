<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran Siswa - {{ $siswa->nama }}</title>
    
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            color: #333d47;
            padding-bottom: 3rem;
        }
        
        .header-bg {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 0;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.15);
            margin-bottom: -4rem;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 10;
        }

        .avatar-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3f51b5 0%, #5c6bc0 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(63, 81, 181, 0.2);
            border: 4px solid white;
        }
        
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .badge-points {
            font-size: 1.8rem;
            font-weight: 800;
            color: #d32f2f;
            display: block;
            line-height: 1;
        }
        
        .status-progress {
            height: 10px;
            border-radius: 5px;
            background-color: #e0e6ed;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .violation-card {
            background: white;
            border-radius: 1.2rem;
            border: 1px solid #eef2f5;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .violation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
        
        .timeline-badge {
            background-color: #fff8e1;
            color: #ffb300;
            padding: 0.4rem 0.8rem;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .timeline-badge.badge-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .timeline-badge.badge-warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .status-container {
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .status-aman { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .status-perhatian { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        .status-pembinaan { background-color: #fff3e0; color: #ef6c00; border: 1px solid #ffe0b2; }
        .status-panggilan_ortu { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .status-rekomendasi { background-color: #fbe9e7; color: #d84315; border: 1px solid #ffccbc; }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header-bg text-center">
        <div class="container">
            <h4 class="fw-bold mb-1 tracking-wide text-uppercase" style="letter-spacing: 2px; opacity: 0.9;">E-LAPORAN SISWA</h4>
            <p class="fs-6 mb-0 opacity-75">Riwayat Pelanggaran & Akumulasi Poin Kedisiplinan</p>
        </div>
    </div>

    <!-- Content Container -->
    <div class="container mt-5">
        
        <!-- Profile & Summary Card -->
        <div class="profile-card">
            <div class="row align-items-center">
                <!-- Avatar & General Info -->
                <div class="col-md-8 d-flex align-items-center flex-column flex-md-row text-center text-md-start mb-4 mb-md-0">
                    <div class="avatar-circle mb-3 mb-md-0 me-md-4">
                        @if($siswa->foto)
                            <img src="{{ asset('storage/' . $siswa->foto) }}" alt="{{ $siswa->nama }}" class="avatar-img"/>
                        @else
                            {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h2 class="fw-bold text-gray-800 mb-1 fs-3">{{ $siswa->nama }}</h2>
                        <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2 fs-7 text-muted">
                            <span>NIS/NISN: <strong>{{ $siswa->nis }} / {{ $siswa->nisn ?? '-' }}</strong></span>
                            <span class="d-none d-md-inline">•</span>
                            <span>Kelas: <strong>{{ $siswa->kelas }} {{ $siswa->jurusan }}</strong></span>
                        </div>
                        <div class="fs-7 text-muted mt-1">
                            <span>Orang Tua/Wali: <strong>{{ $siswa->nama_orang_tua ?? '-' }}</strong></span>
                        </div>
                    </div>
                </div>
                
                <!-- Poin Accumulation Box -->
                <div class="col-md-4 text-center text-md-end border-start-md">
                    <div class="ps-md-4">
                        <span class="text-uppercase tracking-wider fs-8 text-muted fw-bold d-block mb-1">Total Poin Akumulasi</span>
                        <span class="badge-points mb-2">{{ $siswa->total_poin }} <span class="fs-6 text-muted fw-medium">Poin</span></span>
                        
                        @php
                            $status = $siswa->status_pembinaan;
                            $percent = min(100, ($siswa->total_poin / 100) * 100);
                        @endphp
                        
                        <div class="text-start text-md-end">
                            <span class="fs-7 fw-semibold">Status: 
                                <span class="badge {{ $status['badge'] }} fs-7 py-1 px-3">{{ $status['label'] }}</span>
                            </span>
                            <div class="status-progress">
                                <div class="progress-bar {{ $siswa->total_poin > 75 ? 'bg-danger' : ($siswa->total_poin > 50 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dynamic Status Description box for Parents -->
            <div class="status-container status-{{ $status['key'] }} mt-4">
                <div class="d-flex align-items-start">
                    <i class="fa-solid fa-circle-info fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Informasi Tindak Lanjut Sekolah:</h6>
                        <p class="fs-7 mb-0">
                            @if($status['key'] === 'aman')
                                Siswa memiliki catatan kedisiplinan yang sangat baik dan dalam kondisi aman. Terima kasih atas kerja sama Bapak/Ibu dalam membimbing ananda.
                            @elseif($status['key'] === 'perhatian')
                                Ananda memerlukan sedikit perhatian untuk mencegah pelanggaran berlanjut. Mohon ingatkan ananda agar mematuhi tata tertib sekolah.
                            @elseif($status['key'] === 'pembinaan')
                                Akumulasi poin sudah masuk tahap pembinaan. Pihak sekolah (Wali Kelas & Guru BK) akan memberikan pembinaan khusus kepada ananda.
                            @elseif($status['key'] === 'panggilan_ortu')
                                Mohon perhatian penting. Bapak/Ibu Orang Tua/Wali murid diharapkan kehadirannya di sekolah untuk menemui Guru BK guna membahas pembinaan disiplin ananda.
                            @else
                                Akumulasi poin pelanggaran ananda telah melebihi batas toleransi. Diperlukan tindakan khusus dan koordinasi mendalam antara orang tua, BK, dan Kepala Sekolah.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline / Violations List Section -->
        <h4 class="fw-bold text-gray-800 mb-4 px-2 d-flex align-items-center">
            <i class="fa-solid fa-list-check text-primary me-3 fs-5"></i>
            Riwayat Catatan Pelanggaran
        </h4>
        
        <div class="row">
            <div class="col-12">
                @forelse($riwayat as $r)
                    <div class="violation-card p-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="timeline-badge badge-danger">
                                    <i class="fa-solid fa-exclamation-triangle me-1"></i> {{ $r->poin }} Poin
                                </span>
                                <span class="badge bg-light-secondary text-secondary fs-8 fw-semibold px-3 py-1">
                                    {{ $r->pelanggaran->kategori->nama ?? 'Umum' }}
                                </span>
                            </div>
                            <span class="text-muted fs-7 fw-medium">
                                <i class="fa-regular fa-calendar-alt me-1"></i> {{ $r->tanggal_pelanggaran->format('d M Y') }}
                            </span>
                        </div>
                        
                        <h5 class="fw-bold text-gray-900 fs-5 mb-2">{{ $r->pelanggaran->nama_pelanggaran ?? '-' }}</h5>
                        
                        @if($r->catatan)
                            <p class="fs-7 text-muted mb-0 bg-light p-3 rounded border-start border-3 border-secondary-subtle">
                                <strong class="text-gray-800 d-block mb-1">Catatan/Keterangan:</strong>
                                {{ $r->catatan }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-gray-100 p-4">
                        <div class="avatar-circle bg-light-success text-success mx-auto mb-3" style="width: 70px; height: 70px; font-size: 1.8rem;">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h5 class="fw-bold text-gray-800">Hebat! Belum Ada Pelanggaran</h5>
                        <p class="text-muted fs-7 mb-0">Ananda memiliki catatan disiplin yang bersih tanpa pelanggaran.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
