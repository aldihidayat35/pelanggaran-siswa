<?php

namespace App\Http\Controllers\Admin\PelanggaranSiswa;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriPelanggaran::withCount('pelanggaran');

        if ($request->ajax()) {
            $totalRecords = KategoriPelanggaran::count();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            $filteredRecords = $query->count();

            // Handle ordering
            if ($request->filled('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $colDir = $request->input('order.0.dir', 'desc');
                $columns = ['id', 'nama', 'deskripsi', 'pelanggaran_count', 'status', 'id'];
                $colName = $columns[$colIndex] ?? 'id';
                if ($colName === 'pelanggaran_count') {
                    $query->orderBy('pelanggaran_count', $colDir);
                } else {
                    $query->orderBy($colName, $colDir);
                }
            } else {
                $query->latest();
            }

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $kategori = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($kategori as $index => $k) {
                $editUrl = route('pelanggaran-siswa.kategori.edit', $k);
                $deleteUrl = route('pelanggaran-siswa.kategori.destroy', $k);
                $csrf = csrf_field();
                $methodDelete = method_field('DELETE');

                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama' => '<span class="text-gray-800 fw-bold">' . e($k->nama) . '</span>',
                    'deskripsi' => '<span class="text-muted fs-7">' . e($k->deskripsi ?? '-') . '</span>',
                    'pelanggaran_count' => '<span class="badge badge-light-primary">' . $k->pelanggaran_count . '</span>',
                    'status' => '<span class="badge badge-light-' . ($k->status === 'Aktif' ? 'success' : 'danger') . '">' . $k->status . '</span>',
                    'action' => '
                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm"
                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            Aksi
                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                            data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="' . $editUrl . '" class="menu-link px-3">Edit</a>
                            </div>
                            <div class="menu-item px-3">
                                <form method="POST" action="' . $deleteUrl . '"
                                    onsubmit="return confirm(\'Yakin ingin menghapus kategori ini?\')">
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

        return view('admin.pelanggaran-siswa.kategori.index');
    }

    public function create()
    {
        return view('admin.pelanggaran-siswa.kategori.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);

        KategoriPelanggaran::create($validated);

        return redirect()->route('pelanggaran-siswa.kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }

    public function edit(KategoriPelanggaran $kategori)
    {
        return view('admin.pelanggaran-siswa.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, KategoriPelanggaran $kategori)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);

        $kategori->update($validated);

        return redirect()->route('pelanggaran-siswa.kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil diperbarui.');
    }

    public function destroy(KategoriPelanggaran $kategori)
    {
        $kategori->delete();

        return redirect()->route('pelanggaran-siswa.kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil dihapus.');
    }
}
