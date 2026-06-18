<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::getGrouped();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = AppSetting::all();

        foreach ($settings as $setting) {
            $key = $setting->key;

            if ($setting->type === 'image') {
                if ($request->hasFile("settings.{$key}")) {
                    $request->validate([
                        "settings.{$key}" => ['image', 'mimes:jpg,jpeg,png,svg,ico', 'max:2048'],
                    ]);

                    // Delete old file
                    if ($setting->value) {
                        Storage::disk('public')->delete($setting->value);
                    }

                    $path = $request->file("settings.{$key}")->store('settings', 'public');
                    $setting->update(['value' => $path]);
                }
            } elseif ($setting->type === 'boolean') {
                $setting->update(['value' => $request->boolean("settings.{$key}") ? '1' : '0']);
            } else {
                if ($request->has("settings.{$key}")) {
                    $setting->update(['value' => $request->input("settings.{$key}")]);
                }
            }
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }

    public function checkWaHealth(Request $request, \App\Services\WhatsAppService $waService)
    {
        $health = $waService->checkHealth();
        return response()->json($health);
    }

    public function testWaSend(Request $request, \App\Services\WhatsAppService $waService)
    {
        $request->validate([
            'to' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        $res = $waService->sendMessage($request->to, $request->message, null, 'test send');
        return response()->json($res);
    }

    public function waLogs(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\WhatsAppLog::with('siswa');
            
            $totalRecords = \App\Models\WhatsAppLog::count();
            
            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('to', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('siswa', function($sq) use ($search) {
                          $sq->where('nama', 'like', "%{$search}%");
                      });
                });
            }
            
            $filteredRecords = $query->count();
            
            // Order
            if ($request->filled('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $colDir = $request->input('order.0.dir', 'desc');
                $columns = ['id', 'created_at', 'to', 'type', 'message', 'status', 'created_at'];
                $colName = $columns[$colIndex] ?? 'created_at';
                
                if ($colName === 'created_at') {
                    $query->orderBy('created_at', $colDir);
                } else {
                    $query->orderBy($colName, $colDir);
                }
            } else {
                $query->latest();
            }
            
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $logs = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($logs as $index => $log) {
                $statusBadge = match($log->status) {
                    'berhasil' => 'badge-light-success',
                    'gagal' => 'badge-light-danger',
                    'nomor kosong' => 'badge-light-warning',
                    default => 'badge-light-secondary'
                };
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'created_at' => $log->created_at->format('d/m/Y H:i'),
                    'siswa' => $log->siswa ? e($log->siswa->nama) : '<span class="text-muted">-</span>',
                    'to' => e($log->to),
                    'type' => '<span class="badge badge-light-primary">' . e($log->type) . '</span>',
                    'message' => '<div class="text-wrap" style="max-width: 250px;">' . e($log->message) . '</div>',
                    'status' => '<span class="badge ' . $statusBadge . '">' . e($log->status) . '</span>',
                    'response' => '<pre class="fs-8 text-wrap p-2 bg-light rounded" style="max-width: 200px; max-height: 100px; overflow-y: auto;">' . e($log->response) . '</pre>',
                ];
            }
            
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }
        
        abort(404);
    }
}
