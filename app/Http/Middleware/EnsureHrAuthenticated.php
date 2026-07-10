<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHrAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->session()->has('hr_logged_in')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
            }

            return redirect()->route('login');
        }

        if ($roles !== [] && ! in_array($request->session()->get('hr_role'), $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki izin membuka halaman ini.'], 403);
            }

            abort(403, 'Anda tidak memiliki izin membuka halaman ini.');
        }

        return $next($request);
    }
}
