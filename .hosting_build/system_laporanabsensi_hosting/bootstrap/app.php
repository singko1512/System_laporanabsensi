<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->isJson()) {
                return response()->json([
                    'message' => 'Sesi Anda sudah berakhir. Silakan muat ulang halaman lalu coba lagi.',
                    'csrf_expired' => true,
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back(fallback: route('login.form'))
                ->withInput($request->except(['_token', '_method', 'password', 'password_confirmation', 'foto']))
                ->with('error_swal', 'Sesi Anda sudah berakhir. Halaman sudah disegarkan, silakan kirim ulang data Anda.');
        });
    })->create();
