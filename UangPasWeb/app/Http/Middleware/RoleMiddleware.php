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

        // Superadmin Kanwil and Kalapas have unrestricted access
        if ($user->role === 'kanwil_admin' || $user->role === 'kalapas') {
            return $next($request);
        }

        // Check if user's role or permission is in allowed roles
        $authorized = false;
        foreach ($roles as $r) {
            if (str_starts_with($r, 'perm:')) {
                $permName = substr($r, 5);
                if ($user->hasPermission($permName)) {
                    $authorized = true;
                    break;
                }
            } elseif ($user->role === $r) {
                $authorized = true;
                break;
            }
        }

        // Mapping standard route roles to permissions
        if (!$authorized) {
            if (in_array('users', $roles) || in_array('admin_koperasi', $roles) || in_array('kalapas', $roles)) {
                if ($user->hasPermission('users_manage')) {
                    $authorized = true;
                }
            }
        }

        if (!empty($roles) && !$authorized) {
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
