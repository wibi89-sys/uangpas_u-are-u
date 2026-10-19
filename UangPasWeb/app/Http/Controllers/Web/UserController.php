<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UptSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Daftar Petugas & Hak Akses
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();
        $upts = UptSetting::all();
        $availablePermissions = User::availablePermissions();

        return view('admin.users', compact('users', 'upts', 'availablePermissions'));
    }

    /**
     * Tambah Petugas Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'upt_code' => 'required|string',
            'nip' => 'nullable|string',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
        ]);

        $permissionsInput = $request->input('permissions', []);
        $cleanPerms = [];
        foreach (User::availablePermissions() as $key => $meta) {
            $cleanPerms[$key] = !empty($permissionsInput[$key]);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'upt_code' => $request->upt_code,
            'nip' => $request->nip,
            'phone' => $request->phone,
            'permissions' => $cleanPerms,
            'is_active' => true,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Petugas baru ' . $request->name . ' berhasil didaftarkan dengan hak akses khusus.');
    }

    /**
     * Update Data Petugas
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'upt_code' => 'required|string',
            'nip' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $permissionsInput = $request->input('permissions', []);
        $cleanPerms = [];
        foreach (User::availablePermissions() as $key => $meta) {
            $cleanPerms[$key] = !empty($permissionsInput[$key]);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'upt_code' => $request->upt_code,
            'nip' => $request->nip,
            'phone' => $request->phone,
            'permissions' => $cleanPerms,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data & hak akses petugas ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Toggle Aktif / Non-Aktif Akun
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }
        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        return back()->with('success', 'Akun petugas ' . $user->name . ' berhasil ' . $statusStr . '.');
    }

    /**
     * Hapus Akun Petugas
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', 'Akun petugas ' . $name . ' berhasil dihapus dari sistem.');
    }

    /**
     * Simulasi Switch Akun Pengguna Cepat (Untuk kemudahan demonstrasi level role)
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);
        session(['simulated_user_id' => $targetUser->id]);

        return back()->with('success', 'Beralih ke peran: ' . $targetUser->role_label . ' (' . $targetUser->name . ').');
    }
}
