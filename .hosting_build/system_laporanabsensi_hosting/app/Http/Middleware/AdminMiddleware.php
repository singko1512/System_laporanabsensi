<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login.form')->with('error', 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        $currentRole = Auth::user()->role;
        if (! in_array($currentRole, ['admin', 'superadmin'], true)) {
            return redirect()->route('home')->with('error_swal', 'Akses dashboard hanya tersedia untuk Admin dan Super Admin.');
        }

        session([
            'admin_authenticated' => true,
            'admin_role' => $currentRole,
        ]);

        if ($roles !== [] && ! in_array($currentRole, $roles, true)) {
            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('error_swal', 'Akses fitur ini hanya tersedia untuk Super Admin.');
        }

        return $next($request);
    }
}
