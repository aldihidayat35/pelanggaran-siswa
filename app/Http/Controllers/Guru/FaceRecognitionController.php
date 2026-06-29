<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Pelanggaran;
use App\Models\PelanggaranSiswa;
use App\Services\FaceRecognitionService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FaceRecognitionController extends Controller
{
    /**
     * Authorize user access based on role.
     */
    private function authorizeRole()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }
    /**
     * Show camera scan interface.
     */
    public function index(FaceRecognitionService $frService)
    {
        $this->authorizeRole();
        $pelanggaranList = Pelanggaran::where('status', 'Aktif')
            ->with('kategori')
            ->orderBy('nama_pelanggaran')
            ->get();

        // Ambil versi pipeline FR (v1/v2) dari /health untuk ditampilkan di view.
        // Null jika service belum pernah dicek atau sedang down — UI harus handle null.
        $pipelineVersion = $frService->fetchPipelineVersion();
        $riwayatGuru = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori'])
            ->when(auth()->user()->role === 'guru', function ($query) {
                $query->where('dicatat_oleh_user_id', auth()->id());
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('guru.attendance.index', compact('pelanggaranList', 'pipelineVersion', 'riwayatGuru'));
    }

    /**
     * Scan image frame using FaceRecognitionService.
     */
    public function scan(Request $request, FaceRecognitionService $frService)
    {
        $this->authorizeRole();
        $request->validate([
            'image' => ['required', 'string'], // base64 string
        ]);

        $res = $frService->scanFace($request->image);

        if (isset($res['success']) && $res['success'] && isset($res['recognized']) && $res['recognized']) {
            $studentId = $res['student_id'] ?? null;
            if (!$studentId) {
                return response()->json([
                    'success' => true,
                    'recognized' => true,
                    'matched' => false,
                    'message' => 'Wajah dikenali oleh sistem LBPH, tetapi format ID siswa tidak valid.'
                ]);
            }

            $siswa = Siswa::find($studentId);
            if (!$siswa) {
                return response()->json([
                    'success' => true,
                    'recognized' => true,
                    'matched' => false,
                    'message' => 'Wajah dikenali oleh sistem LBPH, tetapi data siswa tidak ditemukan di database Laravel.'
                ]);
            }

            $statusPembinaan = $siswa->status_pembinaan;
            $fotoUrl = $siswa->foto ? asset('storage/' . $siswa->foto) : null;

            return response()->json([
                'success' => true,
                'recognized' => true,
                'matched' => true,
                'message' => 'Siswa berhasil dikenali.',
                'top_match' => $res['top_match'] ?? null,
                'candidates' => $res['candidates'] ?? [],
                'distance' => $res['distance'] ?? ($res['top_match']['distance'] ?? null),
                'best_distance' => $res['best_distance'] ?? ($res['top_match']['best_distance'] ?? null),
                'distance_std' => $res['distance_std'] ?? ($res['top_match']['distance_std'] ?? null),
                'prediction_votes' => $res['prediction_votes'] ?? ($res['top_match']['votes'] ?? null),
                'match_strength' => $res['match_strength'] ?? ($res['top_match']['match_strength'] ?? null),
                'match_level' => $res['match_level'] ?? null,
                'candidate_margin' => $res['candidate_margin'] ?? null,
                'face_detected' => $res['face_detected'] ?? null,
                'quality_score' => $res['quality_score'] ?? null,
                'brightness' => $res['brightness'] ?? null,
                'blur_score' => $res['blur_score'] ?? null,
                'face_box' => $res['face_box'] ?? null,
                'reject_reasons' => $res['reject_reasons'] ?? [],
                'siswa' => [
                    'id' => $siswa->id,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas,
                    'jurusan' => $siswa->jurusan,
                    'no_hp_orang_tua' => $siswa->no_hp_orang_tua,
                    'foto' => $fotoUrl,
                    'total_poin' => $siswa->total_poin,
                    'status_pembinaan' => $statusPembinaan['label'],
                    'status_badge' => $statusPembinaan['badge']
                ]
            ]);
        }

        return response()->json($res);
    }

    /**
     * Store violation from face recognition scan.
     */
    public function storeFromFace(Request $request, WhatsAppService $waService)
    {
        $this->authorizeRole();
        $request->validate([
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
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $pelanggaran = Pelanggaran::findOrFail($request->pelanggaran_id);
        
        $data = [
            'siswa_id' => $request->siswa_id,
            'pelanggaran_id' => $request->pelanggaran_id,
            'tanggal_pelanggaran' => $request->tanggal_pelanggaran,
            'catatan' => $request->catatan,
            'status_penanganan' => 'Belum Diproses', // Default penanganan
            'poin' => $pelanggaran->poin,
            'dicatat_oleh' => Auth::check() ? Auth::user()->name : 'Guru',
            'dicatat_oleh_user_id' => Auth::id(),
        ];

        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('bukti', 'public');
        }

        $pelanggaranSiswa = PelanggaranSiswa::create($data);

        // Send WhatsApp notification automatically
        try {
            $waService->sendNotification($pelanggaranSiswa);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send WA notification: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggaran siswa berhasil dicatat dan disimpan.'
        ]);
    }
}
