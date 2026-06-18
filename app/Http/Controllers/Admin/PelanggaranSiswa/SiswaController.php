<?php

namespace App\Http\Controllers\Admin\PelanggaranSiswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::withSum('pelanggaranSiswa as total_poin', 'poin');

        if ($request->ajax()) {
            $totalRecords = Siswa::count();

            if ($request->filled('kelas')) {
                $query->where('kelas', $request->kelas);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%")
                      ->orWhere('jurusan', 'like', "%{$search}%");
                });
            }

            $filteredRecords = $query->count();

            // Handle ordering
            if ($request->filled('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $colDir = $request->input('order.0.dir', 'desc');
                $columns = ['id', 'nama', 'nis', 'kelas', 'status', 'total_poin', 'id'];
                $colName = $columns[$colIndex] ?? 'id';
                if ($colName === 'total_poin') {
                    $query->orderBy('total_poin', $colDir);
                } else {
                    $query->orderBy($colName, $colDir);
                }
            } else {
                $query->latest();
            }

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $siswa = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($siswa as $index => $s) {
                $showUrl = route('pelanggaran-siswa.siswa.show', $s);
                $editUrl = route('pelanggaran-siswa.siswa.edit', $s);
                $deleteUrl = route('pelanggaran-siswa.siswa.destroy', $s);
                $csrf = csrf_field();
                $methodDelete = method_field('DELETE');
                
                $statusPembinaan = $s->status_pembinaan;

                $avatarHtml = '';
                if ($s->foto) {
                    $avatarHtml = '<div class="symbol-label"><img src="' . asset('storage/' . $s->foto) . '" alt="' . e($s->nama) . '" class="w-100"/></div>';
                } else {
                    $avatarHtml = '<div class="symbol-label fs-3 bg-light-primary text-primary">' . strtoupper(substr($s->nama, 0, 1)) . '</div>';
                }

                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'siswa' => '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">' . $avatarHtml . '</div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 mb-1 fw-bold">' . e($s->nama) . '</span>
                                <span class="text-muted fs-7">' . e($s->jenis_kelamin) . ' - ' . e($s->jurusan ?? '-') . '</span>
                            </div>
                        </div>',
                    'nis' => '
                        <span class="text-gray-800 d-block fs-7">' . e($s->nis) . '</span>
                        <span class="text-muted fs-8">' . e($s->nisn ?? '-') . '</span>',
                    'kelas' => e($s->kelas),
                    'status' => '<span class="badge badge-light-' . ($s->status === 'Aktif' ? 'success' : 'danger') . '">' . $s->status . '</span>',
                    'total_poin' => '
                        <span class="badge ' . $statusPembinaan['badge'] . '">' . ($s->total_poin ?? 0) . ' Poin</span>
                        <span class="text-muted fs-8 d-block">' . e($statusPembinaan['label']) . '</span>',
                    'action' => '
                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm"
                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            Aksi
                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4"
                            data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="' . $showUrl . '" class="menu-link px-3">Detail</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="' . route('pelanggaran-siswa.public-laporan', ['token' => $s->whatsapp_token ?? '']) . '" class="menu-link px-3" target="_blank">Riwayat Laporan</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 btn-kirim-laporan text-primary" data-id="' . $s->id . '" data-nama="' . e($s->nama) . '">Kirim Laporan</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="' . $editUrl . '" class="menu-link px-3">Edit</a>
                            </div>
                            <div class="menu-item px-3">
                                <form method="POST" action="' . $deleteUrl . '"
                                    onsubmit="return confirm(\'Yakin ingin menghapus data siswa ini?\')">
                                    ' . $csrf . '
                                    ' . $methodDelete . '
                                    <button type="submit" class="menu-link px-3 border-0 bg-transparent text-danger w-100 text-start">Hapus</button>
                                </form>
                            </div>
                        </div>'
                ];
            }

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        $kelasList = Siswa::distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.pelanggaran-siswa.siswa.index', compact('kelasList'));
    }

    public function create()
    {
        return view('admin.pelanggaran-siswa.siswa.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20', 'unique:siswa,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:siswa,nisn'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'kelas' => ['required', 'string', 'max:50'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'no_hp_siswa' => ['nullable', 'string', 'max:20'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'no_hp_orang_tua' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        Siswa::create($validated);

        return redirect()->route('pelanggaran-siswa.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        $siswa->load(['pelanggaranSiswa.pelanggaran.kategori']);
        $siswa->loadSum('pelanggaranSiswa as total_poin', 'poin');
        $riwayat = $siswa->pelanggaranSiswa()->with('pelanggaran.kategori')->latest('tanggal_pelanggaran')->get();

        return view('admin.pelanggaran-siswa.siswa.show', compact('siswa', 'riwayat'));
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.pelanggaran-siswa.siswa.edit', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($siswa->id)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('siswa', 'nisn')->ignore($siswa->id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'kelas' => ['required', 'string', 'max:50'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'no_hp_siswa' => ['nullable', 'string', 'max:20'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'no_hp_orang_tua' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);

        if ($request->hasFile('foto')) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa->update($validated);

        return redirect()->route('pelanggaran-siswa.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->foto) {
            Storage::disk('public')->delete($siswa->foto);
        }

        $siswa->delete();

        return redirect()->route('pelanggaran-siswa.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    public function kirimLaporan(Siswa $siswa, \App\Services\WhatsAppService $waService)
    {
        $to = $siswa->no_hp_orang_tua;
        if (empty($to)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP orang tua/wali kosong.',
            ]);
        }

        $template = \App\Models\AppSetting::getValue('wa_violation_template');
        if (empty($template)) {
            $template = 'Yth. Bapak/Ibu Orang Tua/Wali dari {nama_siswa}, berikut link riwayat laporan pelanggaran kedisiplinan ananda: {link_riwayat_laporan}';
        }

        // Format the message
        $message = $waService->formatMessage($template, $siswa);

        $res = $waService->sendMessage($to, $message, $siswa->id, 'kirim link laporan');

        return response()->json([
            'success' => $res['success'],
            'message' => $res['success'] ? 'Laporan berhasil dikirim ke WhatsApp orang tua.' : 'Gagal mengirim laporan: ' . $res['message'],
        ]);
    }
}
