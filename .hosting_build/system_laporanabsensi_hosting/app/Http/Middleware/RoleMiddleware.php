<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login.form')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        if (($user->status_akun ?? 'aktif') !== 'aktif') {
            Auth::logout();

            return redirect()->route('login.form')->with('error_swal', 'Akun Anda tidak aktif.');
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()->route('home')->with('error_swal', 'Role Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
