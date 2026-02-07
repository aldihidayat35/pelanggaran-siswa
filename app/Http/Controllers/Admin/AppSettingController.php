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
}
