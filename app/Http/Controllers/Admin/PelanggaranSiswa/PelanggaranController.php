<?php

namespace App\Http\Controllers\Admin\PelanggaranSiswa;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggaran::with('kategori');

        if ($request->ajax()) {
            $totalRecords = Pelanggaran::count();

            if ($request->filled('kategori_id')) {
                $query->where('kategori_id', $request->kategori_id);
            }

            if ($request->filled('tingkat')) {
                $query->where('tingkat', $request->tingkat);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('kode_pelanggaran', 'like', "%{$search}%")
                      ->orWhere('nama_pelanggaran', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            $filteredRecords = $query->count();

            // Handle ordering
            if ($request->filled('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $colDir = $request->input('order.0.dir', 'desc');
                $columns = ['id', 'kode_pelanggaran', 'nama_pelanggaran', 'kategori_id', 'tingkat', 'poin', 'status', 'id'];
                $colName = $columns[$colIndex] ?? 'id';
                $query->orderBy($colName, $colDir);
            } else {
                $query->latest();
            }

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $pelanggaran = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($pelanggaran as $index => $p) {
                $showUrl = route('pelanggaran-siswa.pelanggaran.show', $p);
                $editUrl = route('pelanggaran-siswa.pelanggaran.edit', $p);
                $deleteUrl = route('pelanggaran-siswa.pelanggaran.destroy', $p);
                $csrf = csrf_field();
                $methodDelete = method_field('DELETE');

                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'kode_pelanggaran' => '<span class="text-gray-800 fw-bold">' . e($p->kode_pelanggaran) . '</span>',
                    'nama_pelanggaran' => '
                        <span class="text-gray-800 fw-semibold d-block">' . e($p->nama_pelanggaran) . '</span>
                        <span class="text-muted fs-7">' . e(\Str::limit($p->deskripsi, 60)) . '</span>',
                    'kategori' => '<span class="text-muted fs-7">' . e($p->kategori->nama ?? '-') . '</span>',
                    'tingkat' => '<span class="badge ' . $p->tingkat_badge . '">' . $p->tingkat . '</span>',
                    'poin' => '<span class="badge badge-light-primary fs-6 fw-bold">' . $p->poin . '</span>',
                    'status' => '<span class="badge badge-light-' . ($p->status === 'Aktif' ? 'success' : 'danger') . '">' . $p->status . '</span>',
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
                                    onsubmit="return confirm(\'Yakin ingin menghapus jenis pelanggaran ini?\')">
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

        $kategoriList = KategoriPelanggaran::where('status', 'Aktif')->orderBy('nama')->get();

        return view('admin.pelanggaran-siswa.pelanggaran.index', compact('kategoriList'));
    }

    public function create()
    {
        $kategoriList = KategoriPelanggaran::where('status', 'Aktif')->orderBy('nama')->get();
        return view('admin.pelanggaran-siswa.pelanggaran.create', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_pelanggaran' => ['required', 'string', 'max:20', 'unique:pelanggaran,kode_pelanggaran'],
            'nama_pelanggaran' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'tingkat' => ['required', Rule::in(['Ringan', 'Sedang', 'Berat'])],
            'poin' => ['required', 'integer', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);

        Pelanggaran::create($validated);

        return redirect()->route('pelanggaran-siswa.pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load('kategori');
        return view('admin.pelanggaran-siswa.pelanggaran.show', compact('pelanggaran'));
    }

    public function edit(Pelanggaran $pelanggaran)
    {
        $kategoriList = KategoriPelanggaran::where('status', 'Aktif')->orderBy('nama')->get();
        return view('admin.pelanggaran-siswa.pelanggaran.edit', compact('pelanggaran', 'kategoriList'));
    }

    public function update(Request $request, Pelanggaran $pelanggaran)
    {
        $validated = $request->validate([
            'kode_pelanggaran' => ['required', 'string', 'max:20', Rule::unique('pelanggaran', 'kode_pelanggaran')->ignore($pelanggaran->id)],
            'nama_pelanggaran' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'tingkat' => ['required', Rule::in(['Ringan', 'Sedang', 'Berat'])],
            'poin' => ['required', 'integer', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);

        $pelanggaran->update($validated);

        return redirect()->route('pelanggaran-siswa.pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran)
    {
        $pelanggaran->delete();

        return redirect()->route('pelanggaran-siswa.pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }
}
