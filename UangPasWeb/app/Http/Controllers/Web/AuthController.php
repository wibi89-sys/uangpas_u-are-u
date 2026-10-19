<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $sampleUsers = User::where('is_active', true)
            ->select('id', 'name', 'email', 'nip', 'role')
            ->orderBy('id')
            ->get();

        return view('auth.login', compact('sampleUsers'));
    }

    /**
     * Proses Login Pengguna
     */
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_id.required' => 'NIP atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginId = trim($request->input('login_id'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Cari berdasarkan NIP atau Email
        $user = User::where('nip', $loginId)
            ->orWhere('email', $loginId)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('login_id', 'remember'))
                ->withErrors(['login_id' => 'Kombinasi NIP/Email dan password tidak cocok.']);
        }

        if (!$user->is_active) {
            return back()
                ->withInput($request->only('login_id'))
                ->withErrors(['login_id' => 'Akun Anda dinonaktifkan oleh administrator UPT. Silakan hubungi pengawas.']);
        }

        // Login ke session Laravel
        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Redirect sesuai peran
        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . $user->role_label . ')');
    }

    /**
     * Proses Logout
     */
    public function logout(Request $request)
    {
        $userName = Auth::user()?->name ?? 'Pengguna';
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi ' . $userName . ' berhasil diakhiri. Terima kasih.');
    }
}
