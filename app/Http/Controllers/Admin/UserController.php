<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->ajax()) {
            $totalRecords = User::count();

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $filteredRecords = $query->count();

            // Handle ordering
            if ($request->filled('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $colDir = $request->input('order.0.dir', 'desc');
                $columns = ['id', 'name', 'role', 'is_active', 'created_at', 'id'];
                $colName = $columns[$colIndex] ?? 'id';
                $query->orderBy($colName, $colDir);
            } else {
                $query->latest();
            }

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $users = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($users as $index => $user) {
                $editUrl = route('admin.users.edit', $user);
                $deleteUrl = route('admin.users.destroy', $user);
                $csrf = csrf_field();
                $methodDelete = method_field('DELETE');
                $isSelf = $user->id === auth()->id();

                $avatarHtml = '';
                if ($user->avatar) {
                    $avatarHtml = '<div class="symbol-label"><img src="' . asset('storage/' . $user->avatar) . '" alt="' . e($user->name) . '" class="w-100"/></div>';
                } else {
                    $avatarHtml = '<div class="symbol-label fs-3 bg-light-primary text-primary">' . strtoupper(substr($user->name, 0, 1)) . '</div>';
                }

                $deleteActionHtml = '';
                if (!$isSelf) {
                    $deleteActionHtml = '
                        <div class="menu-item px-3">
                            <form method="POST" action="' . $deleteUrl . '"
                                onsubmit="return confirm(\'Yakin ingin menghapus user ini?\')">
                                ' . $csrf . '
                                ' . $methodDelete . '
                                <button type="submit" class="menu-link px-3 border-0 bg-transparent text-danger w-100 text-start">Hapus</button>
                            </form>
                        </div>';
                }

                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'user' => '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">' . $avatarHtml . '</div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 mb-1 fw-bold">' . e($user->name) . '</span>
                                <span class="text-muted fs-7">' . e($user->email) . '</span>
                            </div>
                        </div>',
                    'role' => '<span class="badge badge-light-' . ($user->role === 'admin' ? 'danger' : 'primary') . '">' . ucfirst($user->role) . '</span>',
                    'status' => '<span class="badge badge-light-' . ($user->is_active ? 'success' : 'secondary') . '">' . ($user->is_active ? 'Aktif' : 'Nonaktif') . '</span>',
                    'created_at' => $user->created_at->format('d M Y'),
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
                            </div>' . $deleteActionHtml . '
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

        return view('admin.users.index');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
