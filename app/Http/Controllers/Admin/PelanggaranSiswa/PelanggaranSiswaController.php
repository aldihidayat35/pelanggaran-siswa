<?php

namespace App\Http\Controllers\Admin\PelanggaranSiswa;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PelanggaranSiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori']);

        if ($request->ajax()) {
            $totalRecords = PelanggaranSiswa::count();

            if ($request->filled('siswa_id')) {
                $query->where('siswa_id', $request->siswa_id);
            }

            if ($request->filled('status_penanganan')) {
                $query->where('status_penanganan', $request->status_penanganan);
            }

            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->whereHas('siswa', function ($sq) use ($search) {
                        $sq->where('nama', 'like', "%{$search}%")
                           ->orWhere('nis', 'like', "%{$search}%");
                    })->orWhereHas('pelanggaran', function ($pq) use ($search) {
                        $pq->where('nama_pelanggaran', 'like', "%{$search}%")
                           ->orWhere('kode_pelanggaran', 'like', "%{$search}%");
                    })->orWhere('dicatat_oleh', 'like', "%{$search}%");
                });
            }

            if (auth()->user()->role === 'guru') {
                $query->where('dicatat_oleh_user_id', auth()->id());
            }

            $filteredRecords = $query->count();

            // Handle ordering
            if ($request->filled('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $colDir = $request->input('order.0.dir', 'desc');
                $columns = ['id', 'tanggal_pelanggaran', 'siswa_id', 'pelanggaran_id', 'id', 'poin', 'status_penanganan', 'id'];
                $colName = $columns[$colIndex] ?? 'id';
                if ($colName === 'tanggal_pelanggaran') {
                    $query->orderBy('tanggal_pelanggaran', $colDir);
                } elseif ($colName === 'siswa_id') {
                    $query->join('siswa', 'pelanggaran_siswa.siswa_id', '=', 'siswa.id')
                          ->orderBy('siswa.nama', $colDir)
                          ->select('pelanggaran_siswa.*');
                } else {
                    $query->orderBy($colName, $colDir);
                }
            } else {
                $query->orderBy('tanggal_pelanggaran', 'desc');
            }

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $riwayat = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($riwayat as $index => $r) {
                $showUrl = route('pelanggaran-siswa.riwayat.show', $r);
                $editUrl = route('pelanggaran-siswa.riwayat.edit', $r);
                $deleteUrl = route('pelanggaran-siswa.riwayat.destroy', $r);
                $csrf = csrf_field();
                $methodDelete = method_field('DELETE');

                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'tanggal_pelanggaran' => '<span class="text-gray-800">' . e($r->tanggal_pelanggaran->format('d M Y')) . '</span>',
                    'siswa' => '
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold">' . e($r->siswa->nama ?? '-') . '</span>
                            <span class="text-muted fs-8">NIS: ' . e($r->siswa->nis ?? '-') . '</span>
                        </div>',
                    'pelanggaran' => '
                        <span class="text-gray-800">' . e($r->pelanggaran->nama_pelanggaran ?? '-') . '</span>
                        <span class="text-muted fs-8">(' . e($r->dicatat_oleh ?? '-') . ')</span>',
                    'kategori' => '<span class="text-muted fs-7">' . e($r->pelanggaran->kategori->nama ?? '-') . '</span>',
                    'poin' => '<span class="badge badge-light-warning fs-7 fw-bold">' . $r->poin . '</span>',
                    'status' => '<span class="badge ' . $r->status_badge . ' fs-7 fw-semibold">' . $r->status_penanganan . '</span>',
                    'action' => '
                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm"
                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            Aksi
                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                            data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="' . $showUrl . '" class="menu-link px-3">Detail</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="' . $editUrl . '" class="menu-link px-3">Edit</a>
                            </div>
                            <div class="menu-item px-3">
                                <form method="POST" action="' . $deleteUrl . '"
                                    onsubmit="return confirm(\'Yakin ingin menghapus data pelanggaran ini?\')">
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

        $siswaList = Siswa::orderBy('nama')->get();

        return view('admin.pelanggaran-siswa.riwayat.index', compact('siswaList'));
    }

    public function create()
    {
        $siswaList = Siswa::where('status', 'Aktif')->orderBy('nama')->get();
        $pelanggaranList = Pelanggaran::where('status', 'Aktif')->with('kategori')->orderBy('nama_pelanggaran')->get();

        return view('admin.pelanggaran-siswa.riwayat.create', compact('siswaList', 'pelanggaranList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => [
                'required',
                'exists:siswa,id',
                function ($attribute, $value, $fail) {
                    if (!Siswa::where('id', $value)->where('status', 'Aktif')->exists()) {
                        $fail('Siswa yang dipilih harus berstatus Aktif.');
                    }
                },
            ],
            'pelanggaran_id' => [
                'required',
                'exists:pelanggaran,id',
                function ($attribute, $value, $fail) {
                    if (!Pelanggaran::where('id', $value)->where('status', 'Aktif')->exists()) {
                        $fail('Jenis pelanggaran yang dipilih harus berstatus Aktif.');
                    }
                },
            ],
            'tanggal_pelanggaran' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'status_penanganan' => ['required', Rule::in(['Belum Diproses', 'Diproses', 'Selesai'])],
        ]);

        $pelanggaran = Pelanggaran::findOrFail($validated['pelanggaran_id']);
        $validated['poin'] = $pelanggaran->poin;
        $validated['dicatat_oleh'] = Auth::check() ? Auth::user()->name : 'Admin';
        $validated['dicatat_oleh_user_id'] = Auth::id();

        if ($request->hasFile('bukti')) {
            $validated['bukti'] = $request->file('bukti')->store('bukti', 'public');
        }

        $pelanggaranSiswa = PelanggaranSiswa::create($validated);

        try {
            $waService = app(\App\Services\WhatsAppService::class);
            $waService->sendNotification($pelanggaranSiswa);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send WA notification: " . $e->getMessage());
        }

        return redirect()->route('pelanggaran-siswa.riwayat.index')
            ->with('success', 'Pelanggaran siswa berhasil dicatat.');
    }

    public function show(PelanggaranSiswa $pelanggaranSiswa)
    {
        $pelanggaranSiswa->load(['siswa', 'pelanggaran.kategori']);
        return view('admin.pelanggaran-siswa.riwayat.show', compact('pelanggaranSiswa'));
    }

    public function edit(PelanggaranSiswa $pelanggaranSiswa)
    {
        $siswaList = Siswa::orderBy('nama')->get();
        $pelanggaranList = Pelanggaran::with('kategori')->orderBy('nama_pelanggaran')->get();

        return view('admin.pelanggaran-siswa.riwayat.edit', compact('pelanggaranSiswa', 'siswaList', 'pelanggaranList'));
    }

    public function update(Request $request, PelanggaranSiswa $pelanggaranSiswa)
    {
        $validated = $request->validate([
            'siswa_id' => [
                'required',
                'exists:siswa,id',
                function ($attribute, $value, $fail) {
                    if (!Siswa::where('id', $value)->where('status', 'Aktif')->exists()) {
                        $fail('Siswa yang dipilih harus berstatus Aktif.');
                    }
                },
            ],
            'pelanggaran_id' => [
                'required',
                'exists:pelanggaran,id',
                function ($attribute, $value, $fail) {
                    if (!Pelanggaran::where('id', $value)->where('status', 'Aktif')->exists()) {
                        $fail('Jenis pelanggaran yang dipilih harus berstatus Aktif.');
                    }
                },
            ],
            'tanggal_pelanggaran' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'status_penanganan' => ['required', Rule::in(['Belum Diproses', 'Diproses', 'Selesai'])],
        ]);

        $pelanggaran = Pelanggaran::findOrFail($validated['pelanggaran_id']);
        $validated['poin'] = $pelanggaran->poin;

        if ($request->hasFile('bukti')) {
            if ($pelanggaranSiswa->bukti) {
                Storage::disk('public')->delete($pelanggaranSiswa->bukti);
            }
            $validated['bukti'] = $request->file('bukti')->store('bukti', 'public');
        }

        $pelanggaranSiswa->update($validated);

        return redirect()->route('pelanggaran-siswa.riwayat.index')
            ->with('success', 'Pelanggaran siswa berhasil diperbarui.');
    }

    public function destroy(PelanggaranSiswa $pelanggaranSiswa)
    {
        if ($pelanggaranSiswa->bukti) {
            Storage::disk('public')->delete($pelanggaranSiswa->bukti);
        }

        $pelanggaranSiswa->delete();

        return redirect()->route('pelanggaran-siswa.riwayat.index')
            ->with('success', 'Pelanggaran siswa berhasil dihapus.');
    }
}
