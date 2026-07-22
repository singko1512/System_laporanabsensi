<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('admin_authenticated') || !session()->get('admin_authenticated')) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Silakan masukkan PIN Admin terlebih dahulu.');
        }

        return $next($request);
    }
}
