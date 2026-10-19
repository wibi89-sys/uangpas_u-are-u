<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('error', 'Silakan masuk terlebih dahulu untuk mengakses sistem.');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan oleh Administrator. Hubungi pengawas UPT.');
        }

        // Superadmin Kanwil has unrestricted access
        if ($user->role === 'kanwil_admin') {
            return $next($request);
        }

        // Check if user's role is in allowed roles
        if (!empty($roles) && !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki izin untuk tindakan ini (' . $user->role_label . ').'
                ], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Akses Ditolak: Peran Anda (' . $user->role_label . ') tidak memiliki izin untuk mengakses modul tersebut.');
        }

        return $next($request);
    }
}
